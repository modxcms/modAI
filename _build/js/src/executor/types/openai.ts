export type Annotation =
  | {
      type: 'url_citation';
      title: string;
      url: string;
      start_index: number;
      end_index: number;
    }
  | {
      type: 'container_file_citation';
      start_index: number;
      end_index: number;
      file_id: string;
      container_id: string;
    }
  | {
      type: 'file_citation';
      index: number;
      file_id: string;
    }
  | {
      type: 'file_path';
      index: number;
      file_id: string;
    };

export type OutputItem =
  | {
      id: string;
      type: 'message';
      status: string;
      role: 'assistant';
      content: {
        type: 'output_text';
        text: string;
        annotations: Annotation[];
      }[];
    }
  | {
      id: string;
      type: 'function_call';
      status: string;
      arguments: string;
      call_id: string;
      name: string;
    };
