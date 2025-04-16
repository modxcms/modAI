#!/usr/bin/env php
<?php
/**
 * CLI script to index MODX resources and elements with ability to resume after interruption
 * Usage: php index_resources.php [--reset] [--resources] [--elements] [--chunks] [--snippets] [--templates]
 *
 *  Examples:
 *  - Index everything: php modx-indexer.php
 *  - Index all resources: php modx-indexer.php --resources
 *  - Index chunks and templates: php modx-indexer.php --chunks --templates
 *  - Index all elements: php modx-indexer.php --elements
 *  - Reset and index all types: php modx-indexer.php --reset
 *
 *  - If no type is specified, everything will index
 */

use MODX\Revolution\modX;
use MODX\Revolution\modChunk;
use MODX\Revolution\modSnippet;
use MODX\Revolution\modTemplate;
use xPDO\xPDO;

define('MODX_API_MODE', true);
require_once dirname(__DIR__, 4) . '/config.core.php';

if (!(require_once MODX_CORE_PATH . 'vendor/autoload.php')) {
    die('Failed to load vendor/autoload.');
}

$modx = \MODX\Revolution\modX::getInstance(null, [\xPDO\xPDO::OPT_CONN_INIT => [\xPDO\xPDO::OPT_CONN_MUTABLE => true]]);
if (!is_object($modx) || !($modx instanceof \MODX\Revolution\modX)) {
    die('Failed to init MODX');
}

$modx->initialize('mgr');
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget('ECHO');

$fakeSudo = $modx->newObject('modUser');
$fakeSudo->set('id', 1);
$fakeSudo->setSudo(true);
$modx->user = $fakeSudo;

if (!$modx->services->has('modai')) {
    die('modAI not found');
}

/** @var \modAI\modAI | null $modAI */
$modAI = $modx->services->get('modai');

if ($modAI === null) {
    die('Failed to init modAI');
}

$availableTypes = [
    'resource',
    'element',
    'chunk',
    'snippet',
    'template'
];

$reset = in_array('--reset', $argv);

$typesToIndex = [];
foreach ($availableTypes as $option) {
    if (in_array('--' . $option . 's', $argv)) {
        if ($option === 'element') {
            $typesToIndex['chunk'] = 'chunk';
            $typesToIndex['snippet'] = 'snippet';
            $typesToIndex['template'] = 'template';
             continue;
        }

        $typesToIndex[$option] = $option;
    }
}

if (empty($typesToIndex)) {
    $typesToIndex = [
        'resource' => 'resource',
        'chunk' => 'chunk',
        'snippet' => 'snippet',
        'template' => 'template'
    ];
}

class Indexer {
    private $modx;
    private $modAI;
    private $state;
    private $batchSize = 10;
    private $stateFile  = __DIR__ . '/indexer_state.json';
    private $options;

    /**
     * @var \modAI\ContextProviders\Pinecone[]
     */
    private $contextProviders = [];

    public function __construct($modAI, $options) {
        $this->modAI = $modAI;
        $this->modx = $this->modAI->modx;
        $this->options = $options;

        foreach ($this->options['typesToIndex'] as $type) {
            $contextName = $this->modx->getOption("modai.contexts.{$type}s.name");
            if (empty($contextName)) {
                continue;
            }

            /** @var \modAI\Model\ContextProvider $provider */
            $provider = $this->modx->getObject(
                \modAI\Model\ContextProvider::class,
                ['enabled' => true, 'name' => $contextName, 'class' => \modAI\ContextProviders\Pinecone::class]
            );
            if (!$provider) {
                continue;
            }

            $this->contextProviders[$type] = $provider->getContextProviderInstance();
        }

        if (count($this->contextProviders) === 0) {
          throw new Exception('No context providers, check out system settings: "modai.contexts.type.name".');
        }

        $this->loadState();
    }

    private function loadState() {
        if (file_exists($this->stateFile) && !$this->options['reset']) {
            $content = file_get_contents($this->stateFile);
            $this->state = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->initializeState();
            }
        } else {
            $this->initializeState();
        }
    }

    private function initializeState() {
        $this->state = [
            'processed' => ['resource' =>[], 'chunk' =>[], 'snippet' =>[], 'template' =>[]],
            'failed' => ['resource' =>[], 'chunk' =>[], 'snippet' =>[], 'template' =>[]],
            'lastRun' => null,
            'completed' => false
        ];
        $this->saveState();
    }

    private function saveState() {
        file_put_contents($this->stateFile, json_encode($this->state, JSON_PRETTY_PRINT));
    }

    private function log($level, $message) {
        switch ($level) {
            case xPDO::LOG_LEVEL_DEBUG :
                $levelText= 'DEBUG';
                break;
            case xPDO::LOG_LEVEL_INFO :
                $levelText= 'INFO';
                break;
            case xPDO::LOG_LEVEL_WARN :
                $levelText= 'WARN';
                break;
            case xPDO::LOG_LEVEL_ERROR :
                $levelText= 'ERROR';
                break;
            default :
                $levelText= 'FATAL';
        }

      echo '[' . date('Y-m-d H:i:s') . '] ' . $levelText . ': ' . $message . "\n";
    }

    public function run() {
        $this->log(modX::LOG_LEVEL_INFO, 'Starting indexing process...');

        if ($this->state['completed'] === true) {
            $this->log(modX::LOG_LEVEL_INFO, 'Previous indexing completed successfully. Starting over...');
            $this->initializeState();
        }

        $completed = true;
        foreach ($this->options['typesToIndex'] as $type) {
            $resources = $this->getItemsToProcess($type);

            $counter = 0;
            foreach ($resources as $resource) {
                $this->indexResource($type, $resource);
                $counter++;

                if ($counter >= $this->batchSize) {
                    $counter = 0;
                    $this->saveState();
                }
            }

            $completed = $completed && empty($this->state['failed'][$type]);
        }

        $this->state['completed'] = $completed;
        $this->state['lastRun'] = date('Y-m-d H:i:s');
        $this->saveState();

        if ($this->state['completed']) {
            $this->log(modX::LOG_LEVEL_INFO, 'Indexing completed successfully!');
        } else {
          foreach ($this->options['typesToIndex'] as $type) {
              $this->log(
                  modX::LOG_LEVEL_INFO,
                  'Indexing completed with ' . count($this->state['failed'][$type]) . " failed {$type}s."
              );
              $this->log(modX::LOG_LEVEL_INFO, 'Failed IDs: ' . implode(', ', $this->state['failed'][$type]));
          }
        }
    }

    private function getItemsToProcess($type) {
        $where = [];

        $classMap = [
            'resource' => modResource::class,
            'chunk' => modChunk::class,
            'snippet' => modSnippet::class,
            'template' => modTemplate::class,
        ];

        if (!empty($this->state['processed'][$type])) {
            $where['id:NOT IN'] = $this->state['processed'][$type];
        }

        $count = $this->modx->getCount($classMap[$type], $where);
        $this->log(modX::LOG_LEVEL_INFO, 'Found ' . $count . " {$type}s to index.");

        return $this->modx->getIterator($classMap[$type], $where);
    }

    /**
     * @param xPDOObject $item
     * @return void
     */
    private function indexResource($type, $item) {
        if (!isset($this->contextProviders[$type])) {
          return;
        }

        try {
            $data = $item->toArray();
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $value = json_encode($value);
                }

                $data[$key] = strip_tags($value);
            }

            $this->contextProviders[$type]->index($type, $item->get('id'), $data);

            // Add to processed, remove from failed if it was there
            $this->state['processed'][$type][] = $item->id;
            $this->state['failed'][$type] = array_diff($this->state['failed'][$type], [$item->id]);
            $this->log(modX::LOG_LEVEL_INFO, "Indexed $type #{$item->id} successfully");
        } catch (Exception $e) {
            $this->log(modX::LOG_LEVEL_ERROR, "Indexing failed for $type #{$item->id}. Error: " . $e->getMessage());

            // Add to failed, remove from processed if it was there
            $this->state['failed'][$type][] = $item->id;
            $this->state['processed'][$type] = array_diff($this->state['processed'][$type], [$item->id]);
        }
    }
}

try {
    $indexer = new Indexer($modAI, ['reset' => $reset, 'typesToIndex' => $typesToIndex]);
    $indexer->run();
} catch (Exception $e) {
    die('Indexing error: ' . $e->getMessage());
}
