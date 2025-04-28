# Changelog

All notable changes to this project will be documented in this file.

## 0.12.0-beta - 2025-04-28

### 🚀 Features

- Default global.text.model system setting to gpt-4o-mini ([6b54015](https://github.com/modxcms/modAI/commit/6b54015e27897474cbba859fded1413027e2b585))
- Add basic tools support ([5f22a3f](https://github.com/modxcms/modAI/commit/5f22a3f5d4dec278e2e09364eefd74b5505e8fde))
- Add base model to configure agents and store conversations (#15) ([2718b51](https://github.com/modxcms/modAI/commit/2718b5149a855d9b67f8e4e07f640d227bd99b18))
- Add usage data to the service parsers ([18fa71b](https://github.com/modxcms/modAI/commit/18fa71b183440df36ff5df4b44bc7bc389f3a9dd))
- Separate attachments and contexts ([400d6f2](https://github.com/modxcms/modAI/commit/400d6f251b37d26778652f61718c8874ff4d8543))
- Add tool support via DB ([8d49728](https://github.com/modxcms/modAI/commit/8d497285b1c0c301caccbe9f8e87d604b2003b88))
- Agent prompt & agent selector ([6ca5545](https://github.com/modxcms/modAI/commit/6ca554504c18ccab8b14ec6a4c1d913b189a2f8e))
- Add support for context providers ([8a83bbf](https://github.com/modxcms/modAI/commit/8a83bbfceaa193f452b1e4a2c983cc53d599bdfe))
- Add support to override model in an agent ([e8a9cf2](https://github.com/modxcms/modAI/commit/e8a9cf23e4b4b93bad0a94094cd3e0f5988c0cd9))
- Add CMP to manage context providers ([bc3487b](https://github.com/modxcms/modAI/commit/bc3487b2f9f84e52265a62021003e031475b0f13))
- Add CMP to manage tools ([f582428](https://github.com/modxcms/modAI/commit/f5824289deff016072b19d4bafd2e73213ae35c9))
- Use yes/no renderer on boolean columns ([1478da8](https://github.com/modxcms/modAI/commit/1478da8155fa5aa9f56947d644e6d83abea4ce24))
- Add CMP to manage agents ([ddb248d](https://github.com/modxcms/modAI/commit/ddb248d3cdab63b1d5b9a1d046c7d45d4c411574))
- Ui to select agent ([0bf2149](https://github.com/modxcms/modAI/commit/0bf2149830e29ddf2e2b0a6cf1acfdfa9b915604))
- Add basic modx tools ([30b26b1](https://github.com/modxcms/modAI/commit/30b26b1cb42c80d1781eb750989988f6b12e4936))
- Add markdown parser for assistant messages ([f6d7966](https://github.com/modxcms/modAI/commit/f6d79667b37c8bc48624aaf496d35980887b4098))
- Allow html in makrdown content ([021584f](https://github.com/modxcms/modAI/commit/021584fe495189a4a5289ca6674d5915f8c8d385))
- Persist selected agent ([bbbb50b](https://github.com/modxcms/modAI/commit/bbbb50bb0724f0ffebf487b798712ad94a73ede6))
- Add search adn better triggers to agent/tools/cps combos ([4fa8011](https://github.com/modxcms/modAI/commit/4fa8011b7e9e764c78bb26d637934c305fb62c7a))
- Add advanced config to the agent ([2902da4](https://github.com/modxcms/modAI/commit/2902da433b5ae0360ad03b430c91e15bdf15280c))
- Add support for openrouter.ai ([08c2fd9](https://github.com/modxcms/modAI/commit/08c2fd9d369027659537ed4c1ae232b641b2ef58))
- Add model info to the response msg ([6280e64](https://github.com/modxcms/modAI/commit/6280e6464dfb761a1616194c868cc611c169131f))
- Add ACLs for client & backend ([489ee77](https://github.com/modxcms/modAI/commit/489ee77a28dea596a49dfdeeb638c2ece52a00a6))
- Add permissions check to tools ([74d2337](https://github.com/modxcms/modAI/commit/74d2337daea7dbd527c68d63a1a55f76e4e3c59c))
- Add user groups permissions to agents ([4916dd0](https://github.com/modxcms/modAI/commit/4916dd067e36b2e9e4aee25c9babb1ea7a558611))
- Improve scrolling for new messages ([766b794](https://github.com/modxcms/modAI/commit/766b794535e33cdf2124b7223a464adef2613163))
- Add global modAI button with IndexedDB as persist layer ([b1c015f](https://github.com/modxcms/modAI/commit/b1c015f86c31cad6c98f66d43b883206d014560b))
- Use bot icon for the global button ([03b8365](https://github.com/modxcms/modAI/commit/03b8365b255eae5db5b1032db682e1ad7692278a))
- Add an option to index elements into vector db ([a2d5cbb](https://github.com/modxcms/modAI/commit/a2d5cbb6c793be79db6808e2903098f9d32093b0))
- Make sure user has file_create permission and save policy on source when downloading an image ([1e71d3c](https://github.com/modxcms/modAI/commit/1e71d3cfe67825cfaa28cab462922c1195a65b9c))
- Allow to override path in download image endpoint ([f29a942](https://github.com/modxcms/modAI/commit/f29a9428039c71a8aa9ef734983b05d5d7a316eb))
- Add indexing script ([33c7dad](https://github.com/modxcms/modAI/commit/33c7dad532ed788f600b6d356826a6c8a7217e05))
- Hide agent select button if no agents are available ([d1d6300](https://github.com/modxcms/modAI/commit/d1d630018d14eb2aa2910d1506029c9a7f9947f4))
- Update openai API to use developer instead of system, max_completion_tokens instead of max_tokens and add an ability to disable temperature ([8fe8444](https://github.com/modxcms/modAI/commit/8fe84444fa1ac94cce3d01f5cd801db6f88174a3))
- Renderer for selection context ([8f28184](https://github.com/modxcms/modAI/commit/8f2818475ebf6ca0374189195eee8cbcb9eef650))
- Add tooltip to the agent selector ([7c9ebcb](https://github.com/modxcms/modAI/commit/7c9ebcb00f9bc34c76911f266e7dd8be1ae7d272))
- Add title to the global button ([0263cd5](https://github.com/modxcms/modAI/commit/0263cd5476a90505107fefc313139c69899f5ecd))
- Accept json encoded list of provider/tool classes ([5c3631b](https://github.com/modxcms/modAI/commit/5c3631b9a9f9436d85c8f70cb3e95c91b55a901f))
- Rename tool's description to prompt and add internal description to tools & context providers ([97590df](https://github.com/modxcms/modAI/commit/97590dfd49ee882560ef52994a8565e89cc9e76d))
- Allow to customize tool's prompt ([3bcb3d2](https://github.com/modxcms/modAI/commit/3bcb3d27304f533ce126e96e0a0cb4cad73f40d3))
- Add a seed script ([8365ef6](https://github.com/modxcms/modAI/commit/8365ef63b0f41ec9712a03a89049dde4bf74e16a))
- Add description to the prompt & description fields ([f28031d](https://github.com/modxcms/modAI/commit/f28031d6d46b0201673976192132a0cef2d3db3d))
- Improve chat scrolling to show max 3 lines of last user message ([f2582db](https://github.com/modxcms/modAI/commit/f2582db0b1331bfb3cfdae32fa49104d305f13ee))
- Add EditChunk and EditTemplate tools ([6e4c49b](https://github.com/modxcms/modAI/commit/6e4c49be9b1e419bb7334a9653e26f3873c89a5f))
- Adjust image endpoint to support the new gpt-image-1 model ([3761cc1](https://github.com/modxcms/modAI/commit/3761cc154f2f933f130cc79d4982e9acb28cd4ba))
- Make the modal resizable ([b7dca8c](https://github.com/modxcms/modAI/commit/b7dca8c4094019436b44b919cef7c833189b3fd1))
- Add support for multipart/form-data endpoints like editing an image from openai ([e5804df](https://github.com/modxcms/modAI/commit/e5804df93244c9acc066875156cc05b177cdd36a))
- Add maximize/minimize button to the header ([f73c1f5](https://github.com/modxcms/modAI/commit/f73c1f5edb97ef228ccb4db5f0a1a8819ba09ff6))
- Store modal's position to the local storage ([9f4546d](https://github.com/modxcms/modAI/commit/9f4546d36e257cb2cec84ec63e64979c7b058ed1))
- Define default font ([7323daf](https://github.com/modxcms/modAI/commit/7323daf3cbbcc54a6c88feb0b7c11859a17762da))

### 🐛 Bug Fixes

- Remove modx dependency from composer.json ([77c06bf](https://github.com/modxcms/modAI/commit/77c06bf9c177aafc6f68da7db740ecd3954a46ec))
- Add default usage stats for streaming ([b5fd29a](https://github.com/modxcms/modAI/commit/b5fd29a7a0b582b7a6f73362edc6ba1b0447ce74))
- Reenable pinecone config ([9ef6dd1](https://github.com/modxcms/modAI/commit/9ef6dd1d31a08debb82f10c6863db9fe80ab959c))
- Create/update/delete agents ([78d82eb](https://github.com/modxcms/modAI/commit/78d82ebc14289f4e0bc6642fb486ec55d6ad57e4))
- Correct typo in agent prompt description ([4241ada](https://github.com/modxcms/modAI/commit/4241ada31a1fe74d4e33339c6ae8f96c7c8a82a7))
- Dispatch input event when setting value to the textarea ([6830a6a](https://github.com/modxcms/modAI/commit/6830a6ac643a1fdb19f814c7eeb8c8ae37654064))
- Dealy shadow's onload by 1ms to fix occasion no-scroll on first open ([88c1eef](https://github.com/modxcms/modAI/commit/88c1eef7c77771e792802e731869d04a2d1eb17b))
- Prevent error saving duplicate templates and chunks ([2801e3d](https://github.com/modxcms/modAI/commit/2801e3df92a1f7814b7137416afaf39f7f4a8ca5))
- Correct prompt description to refer to editing a chunk instead of creating one ([f0efe4b](https://github.com/modxcms/modAI/commit/f0efe4bfcc7f5927f009a7b9f64f98e7033d29f8))
- Enforce strict array typing for runTool method arguments ([4958e1a](https://github.com/modxcms/modAI/commit/4958e1a71967fc3bb28fa517cdea6f1063ce120a))
- Add missing lexicons ([c17204d](https://github.com/modxcms/modAI/commit/c17204d18738b97e01e180f7eef440ab4cdd8ce0))

### 🚜 Refactor

- Refactor service parsers and streaming handlers ([4c13dc3](https://github.com/modxcms/modAI/commit/4c13dc33bfb493b42073bea3db28defd962167d3))
- Improve descriptions and behavior for consistency ([8e22bf3](https://github.com/modxcms/modAI/commit/8e22bf3074a84d8ae812b4acc754a31265ad1dcc))
- Update parameter 'parameters' to 'arguments' for consistency with ToolInterface ([09a8734](https://github.com/modxcms/modAI/commit/09a8734eb938422ce78959613434969ebcd840b4))

### 📚 Documentation

- Adjust setting names ([248ab85](https://github.com/modxcms/modAI/commit/248ab85c4399d06bb786ca77c24c22b52d201445))
- Improve docs ([478b1cd](https://github.com/modxcms/modAI/commit/478b1cd0221d4ed5469a77837c55130af63791a8))
- Admin ([a2121c3](https://github.com/modxcms/modAI/commit/a2121c39d58c671105c2df2bcd40d5a734d35974))
- Add tool's prompt ([1ca1a92](https://github.com/modxcms/modAI/commit/1ca1a927e66d023d7c2ebd83ddb55a33416fb3da))

### ⚙️ Miscellaneous Tasks

- Fix commit links in the changelog ([1a58beb](https://github.com/modxcms/modAI/commit/1a58beb7a13ba4e10ddb81d6403e9f7385c7e711))
- Apply phpcs fixes ([112d5bc](https://github.com/modxcms/modAI/commit/112d5bce191cd8c4591c7ba9e1cd29662a7d1989))
- Add tables to build ([57dc306](https://github.com/modxcms/modAI/commit/57dc306c82ecb0dee901c7323923f9310cffd55f))
- Use lexicons for error message ([6eec864](https://github.com/modxcms/modAI/commit/6eec86415360aed462fad7037a2a4e064245aacf))
- Remove unused scripts ([70e87b5](https://github.com/modxcms/modAI/commit/70e87b594b52285d33a4eeb84526a347b8003e13))
- Refactor system settings and AI services ([15bb714](https://github.com/modxcms/modAI/commit/15bb71408dfe2a6624900046f86c9209cffd909c))
- Refactor plugins ([9e0df6c](https://github.com/modxcms/modAI/commit/9e0df6ccf7540d9ade3e4021fa81c1f348c961c8))
- Move APIException under Exceptions namespace ([03ce874](https://github.com/modxcms/modAI/commit/03ce874627e9d3b4acdc1fe20a6384a586bd0ab6))
- Move global.d.ts under @types dir ([50cdaf9](https://github.com/modxcms/modAI/commit/50cdaf9839bea262e42ee89fb6382255384dc484))
- Mark the mgr namespace in executor as deprecated, will be removed in major release ([c8888cb](https://github.com/modxcms/modAI/commit/c8888cb55e3a837a6d1c5262a0625ba6c55759dc))
- Separate type imports ([95c697c](https://github.com/modxcms/modAI/commit/95c697cbb28d373b05732c2ddf90f7b0616e498d))
- Remove test seed script & delete category tool ([26ab011](https://github.com/modxcms/modAI/commit/26ab011ba399c4ffbb367f6f05de9278502b9da1))
- Add Debug helper class ([10495e7](https://github.com/modxcms/modAI/commit/10495e7ef1d66ebaa6b4accfa8edcc038c24ba8c))
- Add EditChunk and EditTemplate tools to seed script ([eb70146](https://github.com/modxcms/modAI/commit/eb70146cc105af80c686eec1eb86bc909c3aa85e))

## 0.11.0-pl - 2025-03-20

### 🚀 Features

- Convert from processors to custom API and enable streaming when using server execution ([e55a8b4](https://github.com/modxcms/modAI/commit/e55a8b489035b926895e21674803809f3fbcb30b))
- Abort stream on error ([def1057](https://github.com/modxcms/modAI/commit/def1057694a61399985133fb61ad89d0ca4b4c5d))
- Add streaming parser for claude ([960a20e](https://github.com/modxcms/modAI/commit/960a20e2ae204e40f17f67b4b190ea7f211f013b))
- Add a support for local chat for free text prompts ([62307df](https://github.com/modxcms/modAI/commit/62307df2dda2a4ed6bd8c5cd8e65c6462ec5bbab))
- Replace ext.js free text prompt with a custom ui ([9b3dfe7](https://github.com/modxcms/modAI/commit/9b3dfe7eec99b44afb75d014cd4340a1e8d018b5))
- Create loading overlay on inputs while generating forced prompt ([0584fcb](https://github.com/modxcms/modAI/commit/0584fcb46ddf81eb68b19c5fe7801ae97900cb02))
- Convert generate image modal to the new UI ([a712a28](https://github.com/modxcms/modAI/commit/a712a28b624cb98432fba6b3529d74c7f4d98900))
- Add an ability to switch local chat mode between text and image ([f91dd91](https://github.com/modxcms/modAI/commit/f91dd911b31d9aba6435a4744921f8a919b558e5))
- Support vision from free text prompts ([b0c5651](https://github.com/modxcms/modAI/commit/b0c565137c3e65ef46c74ecddb451f9773acd8d3))
- Add clear chat button ([f27ac52](https://github.com/modxcms/modAI/commit/f27ac52232aea61f9e2f8df53bdf5e87298877c4))
- Add generateButton to the UI api ([11f5426](https://github.com/modxcms/modAI/commit/11f5426c566d06340f819725b0da5b05e40eaded))
- Add getter methods for css & js files ([96c57fe](https://github.com/modxcms/modAI/commit/96c57fef9e894a6c00fb402d742adfe8cae48f7b))
- Block closing local chat modal while generating response ([41bcafd](https://github.com/modxcms/modAI/commit/41bcafd40168b656077a60ac65e32640c0058e43))
- Render modAI elements in shadow dom ([79a9fdb](https://github.com/modxcms/modAI/commit/79a9fdbc9948a13c944a04f1a5c78e66b5efc066))
- Streamline initing of modAI and it's security ([0b5341b](https://github.com/modxcms/modAI/commit/0b5341b4b3a3747034b10069d97a7c997e41ab54))
- Accept name / id as mediaSource prop for Download/Image API endpoint ([1d5ac94](https://github.com/modxcms/modAI/commit/1d5ac946802db4a5a70598983df9180bf16bfeaf))
- Add support for custom translation functions ([fdbb562](https://github.com/modxcms/modAI/commit/fdbb5627d7724a4e04d0c429cad2e7a6efa535e4))

### 🐛 Bug Fixes

- Correctly propagate errors from the ai proxy ([a6f5941](https://github.com/modxcms/modAI/commit/a6f5941c32306e741db7b70e7fc36c7501e4fc68))

### 📚 Documentation

- Update README.md ([a2ca0cc](https://github.com/modxcms/modAI/commit/a2ca0ccb78ceec8f2281b01bcc6036ca3b8b7338))
- Update README.md ([7411d03](https://github.com/modxcms/modAI/commit/7411d03616223a996622006ebd1ab9a6ae5e88c3))

### 🎨 Styling

- Migrate to StyleX ([6c9c5f2](https://github.com/modxcms/modAI/commit/6c9c5f24f18aef2fccdf8d39ed0b6c5852cd2495))
- New UI ([da185db](https://github.com/modxcms/modAI/commit/da185dbc52b8cc0d0679212f16c3bac7b4b9b196))

### ⚙️ Miscellaneous Tasks

- Migrate to TypeScript ([a4fd9bc](https://github.com/modxcms/modAI/commit/a4fd9bcb25d6a61d908b99483e9a30229fb896db))
- Migrate the resource buttons to TypeScript ([31874b9](https://github.com/modxcms/modAI/commit/31874b91eda9c989c13e1b547862a1a780a204c4))
- Update context prompt ([613349b](https://github.com/modxcms/modAI/commit/613349b65c36dd0c4c8a2f533f91c904899e54f5))
- Add types to localChat styles ([fe4ab40](https://github.com/modxcms/modAI/commit/fe4ab4098bd1cd89aa0ff43a3342fafcc4fd47cc))
- Add eslint and separate localChat to several files ([1408c26](https://github.com/modxcms/modAI/commit/1408c260b906deeefa3fc04136b1e4f546fbeb15))
- Move from webpack to esbuild ([58e7125](https://github.com/modxcms/modAI/commit/58e71252627cc81d36688d3e3d538dedcb8d3aab))
- Improve keyboard navigation ([0c22e73](https://github.com/modxcms/modAI/commit/0c22e7399d6d701ff3d3a808d427374be39815b7))
- Add lexicons for global.image.style setting ([43a60da](https://github.com/modxcms/modAI/commit/43a60da1408c3eb984cc3388ffb7bb0f7422f4a0))
- Create FUNDING.yml ([836e024](https://github.com/modxcms/modAI/commit/836e02439ede7b15ab8e7845ee2ba7bc475fd344))
- Separate RequiredSettingException to own file ([96cb930](https://github.com/modxcms/modAI/commit/96cb930dfe697ffbd059e7118ab9d76d5028f868))
- Use lexicons ([6b08176](https://github.com/modxcms/modAI/commit/6b081769bed4a9cf657f117d2c54af22b999ad36))

## 0.10.0-beta - 2025-02-28

### 🚀 Features

- Enable calling AI services on serverside, instead of from the client ([64edf9f](https://github.com/modxcms/modAI/commit/64edf9f760e2c0b7a94d6c8e6c439f8b3d4baee1))
- Refactor multiple cache handlers to a single history handler ([a150940](https://github.com/modxcms/modAI/commit/a1509403724e170b565e10fee02d3d2c219e298e))
- Refactor system settings, add namespace support, add context support to the free text prompt ([e6c3039](https://github.com/modxcms/modAI/commit/e6c30396ec32fee902d9292a9cf23d0a129c0414))
- Add fullUrl to the return array in the image download processor ([5869b40](https://github.com/modxcms/modAI/commit/5869b40ceb987d55b96635bdd399bedf7d3cc008))
- Create JS API for modAI processors ([b2b4b78](https://github.com/modxcms/modAI/commit/b2b4b7892e880242f133f9e0741c946a1647f607))
- Simplify the JS API ([dee659e](https://github.com/modxcms/modAI/commit/dee659edef8e0b58a4d06dee7cafe2ce97228fa0))
- Add style option for image models ([61371c3](https://github.com/modxcms/modAI/commit/61371c342f6c3ef93e5362556b378f14a6e9c2d1))
- Add support for passing custom options to each model ([ff25a6f](https://github.com/modxcms/modAI/commit/ff25a6f5d70d32fd094bf202151b223e97ae44a6))
- Merge all system prompts to a single message ([314c9ca](https://github.com/modxcms/modAI/commit/314c9cac2c4b1ab94bf7f7cc9ac84fd618cf1a0b))
- Add support for client side streaming (chatgpt & gemini) ([0dc2c68](https://github.com/modxcms/modAI/commit/0dc2c68c4110dbb27285c7d8c0aefdcde5734713))
- Adjust default vision prompt ([afb4cbd](https://github.com/modxcms/modAI/commit/afb4cbd64ebf3e9173d4d7d3d8394619c89bf0e1))
- Add cache buster for JS files ([a7d4949](https://github.com/modxcms/modAI/commit/a7d49497dbe5885704ee5fe2c42eae003bcfa897))

### 🐛 Bug Fixes

- Fix return types from Settings helper class ([825c33e](https://github.com/modxcms/modAI/commit/825c33e397937c9fb6ce4f99a5979ee19b4da7b7))
- Load lexicons from processors ([6587afd](https://github.com/modxcms/modAI/commit/6587afd39187e5c0251ca43c57ac8a14aece9104))
- Fix checking for empty value when getting system settings ([ac4dc2b](https://github.com/modxcms/modAI/commit/ac4dc2bc274df41a725498c8577d4449cbdc2e3e))

### 🚜 Refactor

- Unify fieldName as field property for processors ([f90eaa7](https://github.com/modxcms/modAI/commit/f90eaa74dfd46e5dd26db830d57b03df6ff51ca3))

### 📚 Documentation

- Add markup and references to docs ([2420b20](https://github.com/modxcms/modAI/commit/2420b209303d75d2f5a47b466bc5540cf6ec77e9))
- Note about how/when AI requests execute ([38ef3b3](https://github.com/modxcms/modAI/commit/38ef3b39298accb86b85a410899e5583514f45eb))
- Describe new settings structure and streaming ([b0767a0](https://github.com/modxcms/modAI/commit/b0767a03d6881b54056dcc6995f3e12b3af1feff))

### ⚙️ Miscellaneous Tasks

- Automate build process ([0c64fe2](https://github.com/modxcms/modAI/commit/0c64fe2de8dd166eb7fba4fc6437e000385a1ffc))
- Remove html comments from group name ([b1e05dd](https://github.com/modxcms/modAI/commit/b1e05ddabee1f498906b6465dbdd95d32b88e60b))
- Add translation for modai.global.text.context_prompt system setting ([998b2e4](https://github.com/modxcms/modAI/commit/998b2e4bfdee75d448dcc366ac22a0d86e94c85a))
- Add link to original announcement to the readme ([c51af55](https://github.com/modxcms/modAI/commit/c51af5548cb52d3439ea6c997f4dad418258835c))

## 0.9.0-beta - 2025-02-20

### 🚀 Features

- Init the project ([23cc847](https://github.com/modxcms/modAI/commit/23cc847ef629a5b6bf612a1aac511789823d3b72))
- Hook up to pagetitle, longtitle, description and introtext ([89483e8](https://github.com/modxcms/modAI/commit/89483e8c0398ca41c19bf9d2359e84c561c60b15))
- Store current field's value to the history ([2f7add3](https://github.com/modxcms/modAI/commit/2f7add355ee8040e208597d7935e93e3a4b25dbe))
- Generate images ([dbcdd3a](https://github.com/modxcms/modAI/commit/dbcdd3a16e724eec68337b35b3c3f5a8b4dabaab))
- Add quality to image generation and update baseline prompts ([9716dc2](https://github.com/modxcms/modAI/commit/9716dc2207c9d370a8b939216d9405f33e568fef))
- Hide wait msg on failure and show an error message instead ([0421424](https://github.com/modxcms/modAI/commit/0421424c5398502fb3ff54ec63cd449a8398ea5a))
- Adjust UI & add FreeText prompt ([4ed2c92](https://github.com/modxcms/modAI/commit/4ed2c921a6ce8dfc6a733e0b637dd21f2071f144))
- Configurable tvs & resource fields ([8e8ad03](https://github.com/modxcms/modAI/commit/8e8ad0387ce20b1ed66921503a958b9d5f046f7d))
- Add setting for base output ([3d88b08](https://github.com/modxcms/modAI/commit/3d88b080e448786e6f13ff2960afff61fdd772ec))
- Updating area name and consistent cross-browser modAI button styling ([711113d](https://github.com/modxcms/modAI/commit/711113dbfe26a1fd1b6b5dbfdb26d8e6c99940d1))
- Add global.base.output ([77e9379](https://github.com/modxcms/modAI/commit/77e93791e4555b3cac6d55cb3744c63686890641))
- Allow override base.output ([093e371](https://github.com/modxcms/modAI/commit/093e371abe8c3d5ebf63cc887ebc0e6496301791))
- Add support for gemini ([dbc3bd0](https://github.com/modxcms/modAI/commit/dbc3bd046ca8eb0943140199b53d7d727ca6dc31))
- Attach modAI on textareas and rte TVs ([259dd34](https://github.com/modxcms/modAI/commit/259dd34fe62a178aca3945934c3d2dfe543a8388))
- Add support for anthropic models (claude) ([3bea523](https://github.com/modxcms/modAI/commit/3bea523aa380995ea7b7f733f0198a570abb3bd0))
- Consolidate api services ([5150c47](https://github.com/modxcms/modAI/commit/5150c471d1557ee8d7a0df42eab5017979a63837))
- Add support for custom api, compatible with openai ([fb04ee6](https://github.com/modxcms/modAI/commit/fb04ee69404f6e139e12fa56185810408f9d5544))
- Add system setting to configure download path for generated images ([83c14b6](https://github.com/modxcms/modAI/commit/83c14b6a10924c64d01f2ae8da50c6899e69b53e))
- Add support for image generation in gemini models ([83492c9](https://github.com/modxcms/modAI/commit/83492c9d31ee93f65681854bd1ac78753331c132))
- Add vision support for gemini ([2747fa7](https://github.com/modxcms/modAI/commit/2747fa7a62447e7677e7c75b19fbbfc2381bb366))
- Allow model/prompt overriding for vision per field ([94a2414](https://github.com/modxcms/modAI/commit/94a2414aab0c33a5d98b0304b6a4533b2da1159e))
- Move calling AI service to the client side ([7ef59bd](https://github.com/modxcms/modAI/commit/7ef59bd2df8bdead0a07021d68e759574c66cca3))

### 🐛 Bug Fixes

- Add missing import ([90a2f66](https://github.com/modxcms/modAI/commit/90a2f66eab46db6b751a9c955d0ae6526fb19d18))
- Update assetsUrl path ([68780fa](https://github.com/modxcms/modAI/commit/68780fa50b99aa46ce89e3bc4696620a63e43c7c))
- Fix saving altTag ([9e62f83](https://github.com/modxcms/modAI/commit/9e62f839b253cb3c374e12f200e9ef146380dd89))
- Fix name of global base prompt ([a68bf91](https://github.com/modxcms/modAI/commit/a68bf91862e3ac6f9c4e3678b2848f7448577ae4))
- Fix global base prompt in free text ([faaf61d](https://github.com/modxcms/modAI/commit/faaf61dd172b4d8e674dabbc7d24ae8be41a92ac))
- Fix grabbing chatgpt key ([a173a55](https://github.com/modxcms/modAI/commit/a173a552b94a4158c621fa47c2d48a61efbf5d1d))
- Disable timeout when calling prompt processors ([56b33d8](https://github.com/modxcms/modAI/commit/56b33d84f55019c30c0e768124f88fb582181f33))
- Fix return of generateImage from custom chatgpt integration ([8539f35](https://github.com/modxcms/modAI/commit/8539f350a5d0bbd34eeaed9297c40b4fe959d3a4))
- Use getImageFieldSetting when generating an image ([95f5d90](https://github.com/modxcms/modAI/commit/95f5d90c0a46841e4b176218b681f73fcc4b60c2))

### 📚 Documentation

- More descriptions and initial documentation ([3cefccf](https://github.com/modxcms/modAI/commit/3cefccfbdd7c7ab5708858445f15a41e1087f4c8))
- Document base prompt ([e100400](https://github.com/modxcms/modAI/commit/e1004008f861e804cd8ad49e420fcf4e2f1e5c5b))
- Update README.md ([c2e4e17](https://github.com/modxcms/modAI/commit/c2e4e1779e6760039f8c18b07bd5ca29eab504cb))
- Fixing readme typos and clarifications ([758349f](https://github.com/modxcms/modAI/commit/758349f6aae5b850c00e128f3b6dedcd545e0609))
- Streamline README ([deaee2f](https://github.com/modxcms/modAI/commit/deaee2ff4d098514f56676e5f70dbe942140b783))
- Clarify the README.md even more, because I can't stop ([62ae55d](https://github.com/modxcms/modAI/commit/62ae55df40da60e2fe1c73bb6686834cd4c4d745))
- Update instructions for TV handling + default image to wide ([634e8e0](https://github.com/modxcms/modAI/commit/634e8e0692eaff98861b769e4cff8a2055d9be3c))
- Create LICENSE ([5f24386](https://github.com/modxcms/modAI/commit/5f243867c88772e323766a24d05a11d967055b04))
- Move readme ([d65c986](https://github.com/modxcms/modAI/commit/d65c9863c2ac05c8b3bc96651fd693ff10b82517))
- Documentation updates and Settings names ([808b6d4](https://github.com/modxcms/modAI/commit/808b6d43ed0404eb9981879f2ee3e1c4fc074496))
- Multi-model and custom model usage instructions ([97aa5a0](https://github.com/modxcms/modAI/commit/97aa5a0582ec84d7916d917fdf66ce992fa724f1))
- Fix typos in README ([a89293b](https://github.com/modxcms/modAI/commit/a89293b5d67e0e9dbe4280b3fb05217e50add98e))
- Adjust order and add service specific docs ([0700b38](https://github.com/modxcms/modAI/commit/0700b381c5b22bfea211cd58d2d3aae0ea31459c))
- Add Gemini Image + Vision ([709bd5d](https://github.com/modxcms/modAI/commit/709bd5dada62dd6bcd21bb3ba0c9425ad30ba60c))
- Convert readme to writerside ([fb240bd](https://github.com/modxcms/modAI/commit/fb240bda15c5b7b24f86136acf27339ccbbecd58))
- Set docs to build while in private mode ([e2ccdc7](https://github.com/modxcms/modAI/commit/e2ccdc75f7fd9338654392e51775841c4442c4ce))
- Fix link to docs and add theme ([a13cdc0](https://github.com/modxcms/modAI/commit/a13cdc08ab794aed10a753ee37e0ab439b930043))

### 🎨 Styling

- CSS for nav buttons ([e116c94](https://github.com/modxcms/modAI/commit/e116c943150487bb86cb800698ef6dafa898bb4e))
- Make buttons great again ([76a1f04](https://github.com/modxcms/modAI/commit/76a1f04ce75b45521e9ccb14e638696651fa7126))
- Initial tweaks for Image+ alt button (still need 3px or so top margin) ([ed89a1d](https://github.com/modxcms/modAI/commit/ed89a1d39fcbb63f11064427f9f6d2f1dfd7ee92))
- Fix for minor alignment on alt text icon ([1f3afb6](https://github.com/modxcms/modAI/commit/1f3afb67b72a4a3e0934177e2e060261ac42e359))
- Adjust AI button look ([e7e4929](https://github.com/modxcms/modAI/commit/e7e4929afc7d29797c60c5e39c8bf4192953d7ae))
- Increase height of response textarea in the free text prompt window ([c7533da](https://github.com/modxcms/modAI/commit/c7533daf64644220865de926502cbb6e6fa81bc6))
- Visually simplify button borders ([1b48a84](https://github.com/modxcms/modAI/commit/1b48a84ce5b590584e521c0814ed358b03e0e90e))

### ⚙️ Miscellaneous Tasks

- Version bump ([19fcae4](https://github.com/modxcms/modAI/commit/19fcae4af85a7c24e1f4194db859f376e61c9170))
- Add git-cliff config ([74c2b0d](https://github.com/modxcms/modAI/commit/74c2b0dd29bcad577090b753751e593588b0dea0))
- Add lexicons for system settings ([c00c82e](https://github.com/modxcms/modAI/commit/c00c82ee633e79ec2b81e4d77486a933501999d5))
- Clean up IDs in action ([0c35a59](https://github.com/modxcms/modAI/commit/0c35a59b0790a0e44204005f35550aa26b8a8a0a))
- Convert all text to lexicons ([bd45280](https://github.com/modxcms/modAI/commit/bd452805a79aadeb35e17370bb38f1f4f52ad354))
- Ignore config.core.php file ([222aeab](https://github.com/modxcms/modAI/commit/222aeabbb209da9ef8f8e3efdfaff78f1ff8e3a5))
- Changelog, version bump ([e3eb3a2](https://github.com/modxcms/modAI/commit/e3eb3a27ae5c014d2ac1a4278da8126ae6bdf1e2))


