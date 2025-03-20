# Changelog

All notable changes to this project will be documented in this file.

## 0.11.0-pl - 2025-03-20

### 🚀 Features

- Convert from processors to custom API and enable streaming when using server execution ([e55a8b4](e55a8b489035b926895e21674803809f3fbcb30b))
- Abort stream on error ([def1057](def1057694a61399985133fb61ad89d0ca4b4c5d))
- Add streaming parser for claude ([960a20e](960a20e2ae204e40f17f67b4b190ea7f211f013b))
- Add a support for local chat for free text prompts ([62307df](62307df2dda2a4ed6bd8c5cd8e65c6462ec5bbab))
- Replace ext.js free text prompt with a custom ui ([9b3dfe7](9b3dfe7eec99b44afb75d014cd4340a1e8d018b5))
- Create loading overlay on inputs while generating forced prompt ([0584fcb](0584fcb46ddf81eb68b19c5fe7801ae97900cb02))
- Convert generate image modal to the new UI ([a712a28](a712a28b624cb98432fba6b3529d74c7f4d98900))
- Add an ability to switch local chat mode between text and image ([f91dd91](f91dd911b31d9aba6435a4744921f8a919b558e5))
- Support vision from free text prompts ([b0c5651](b0c565137c3e65ef46c74ecddb451f9773acd8d3))
- Add clear chat button ([f27ac52](f27ac52232aea61f9e2f8df53bdf5e87298877c4))
- Add generateButton to the UI api ([11f5426](11f5426c566d06340f819725b0da5b05e40eaded))
- Add getter methods for css & js files ([96c57fe](96c57fef9e894a6c00fb402d742adfe8cae48f7b))
- Block closing local chat modal while generating response ([41bcafd](41bcafd40168b656077a60ac65e32640c0058e43))
- Render modAI elements in shadow dom ([79a9fdb](79a9fdbc9948a13c944a04f1a5c78e66b5efc066))
- Streamline initing of modAI and it's security ([0b5341b](0b5341b4b3a3747034b10069d97a7c997e41ab54))
- Accept name / id as mediaSource prop for Download/Image API endpoint ([1d5ac94](1d5ac946802db4a5a70598983df9180bf16bfeaf))
- Add support for custom translation functions ([fdbb562](fdbb5627d7724a4e04d0c429cad2e7a6efa535e4))

### 🐛 Bug Fixes

- Correctly propagate errors from the ai proxy ([a6f5941](a6f5941c32306e741db7b70e7fc36c7501e4fc68))

### 📚 Documentation

- Update README.md ([a2ca0cc](a2ca0ccb78ceec8f2281b01bcc6036ca3b8b7338))
- Update README.md ([7411d03](7411d03616223a996622006ebd1ab9a6ae5e88c3))

### 🎨 Styling

- Migrate to StyleX ([6c9c5f2](6c9c5f24f18aef2fccdf8d39ed0b6c5852cd2495))
- New UI ([da185db](da185dbc52b8cc0d0679212f16c3bac7b4b9b196))

### ⚙️ Miscellaneous Tasks

- Migrate to TypeScript ([a4fd9bc](a4fd9bcb25d6a61d908b99483e9a30229fb896db))
- Migrate the resource buttons to TypeScript ([31874b9](31874b91eda9c989c13e1b547862a1a780a204c4))
- Update context prompt ([613349b](613349b65c36dd0c4c8a2f533f91c904899e54f5))
- Add types to localChat styles ([fe4ab40](fe4ab4098bd1cd89aa0ff43a3342fafcc4fd47cc))
- Add eslint and separate localChat to several files ([1408c26](1408c260b906deeefa3fc04136b1e4f546fbeb15))
- Move from webpack to esbuild ([58e7125](58e71252627cc81d36688d3e3d538dedcb8d3aab))
- Improve keyboard navigation ([0c22e73](0c22e7399d6d701ff3d3a808d427374be39815b7))
- Add lexicons for global.image.style setting ([43a60da](43a60da1408c3eb984cc3388ffb7bb0f7422f4a0))
- Create FUNDING.yml ([836e024](836e02439ede7b15ab8e7845ee2ba7bc475fd344))
- Separate RequiredSettingException to own file ([96cb930](96cb930dfe697ffbd059e7118ab9d76d5028f868))
- Use lexicons ([6b08176](6b081769bed4a9cf657f117d2c54af22b999ad36))

## 0.10.0-beta - 2025-02-28

### 🚀 Features

- Enable calling AI services on serverside, instead of from the client ([64edf9f](64edf9f760e2c0b7a94d6c8e6c439f8b3d4baee1))
- Refactor multiple cache handlers to a single history handler ([a150940](a1509403724e170b565e10fee02d3d2c219e298e))
- Refactor system settings, add namespace support, add context support to the free text prompt ([e6c3039](e6c30396ec32fee902d9292a9cf23d0a129c0414))
- Add fullUrl to the return array in the image download processor ([5869b40](5869b40ceb987d55b96635bdd399bedf7d3cc008))
- Create JS API for modAI processors ([b2b4b78](b2b4b7892e880242f133f9e0741c946a1647f607))
- Simplify the JS API ([dee659e](dee659edef8e0b58a4d06dee7cafe2ce97228fa0))
- Add style option for image models ([61371c3](61371c342f6c3ef93e5362556b378f14a6e9c2d1)), resolves #11
- Add support for passing custom options to each model ([ff25a6f](ff25a6f5d70d32fd094bf202151b223e97ae44a6))
- Merge all system prompts to a single message ([314c9ca](314c9cac2c4b1ab94bf7f7cc9ac84fd618cf1a0b))
- Add support for client side streaming (chatgpt & gemini) ([0dc2c68](0dc2c68c4110dbb27285c7d8c0aefdcde5734713))
- Adjust default vision prompt ([afb4cbd](afb4cbd64ebf3e9173d4d7d3d8394619c89bf0e1))
- Add cache buster for JS files ([a7d4949](a7d49497dbe5885704ee5fe2c42eae003bcfa897))

### 🐛 Bug Fixes

- Fix return types from Settings helper class ([825c33e](825c33e397937c9fb6ce4f99a5979ee19b4da7b7))
- Load lexicons from processors ([6587afd](6587afd39187e5c0251ca43c57ac8a14aece9104))
- Fix checking for empty value when getting system settings ([ac4dc2b](ac4dc2bc274df41a725498c8577d4449cbdc2e3e))

### 🚜 Refactor

- Unify fieldName as field property for processors ([f90eaa7](f90eaa74dfd46e5dd26db830d57b03df6ff51ca3))

### 📚 Documentation

- Add markup and references to docs ([2420b20](2420b209303d75d2f5a47b466bc5540cf6ec77e9))
- Note about how/when AI requests execute ([38ef3b3](38ef3b39298accb86b85a410899e5583514f45eb))
- Describe new settings structure and streaming ([b0767a0](b0767a03d6881b54056dcc6995f3e12b3af1feff))

### ⚙️ Miscellaneous Tasks

- Automate build process ([0c64fe2](0c64fe2de8dd166eb7fba4fc6437e000385a1ffc))
- Remove html comments from group name ([b1e05dd](b1e05ddabee1f498906b6465dbdd95d32b88e60b))
- Add translation for modai.global.text.context_prompt system setting ([998b2e4](998b2e4bfdee75d448dcc366ac22a0d86e94c85a))
- Add link to original announcement to the readme ([c51af55](c51af5548cb52d3439ea6c997f4dad418258835c))

## 0.9.0-beta - 2025-02-20

### 🚀 Features

- Init the project ([23cc847](23cc847ef629a5b6bf612a1aac511789823d3b72))
- Hook up to pagetitle, longtitle, description and introtext ([89483e8](89483e8c0398ca41c19bf9d2359e84c561c60b15))
- Store current field's value to the history ([2f7add3](2f7add355ee8040e208597d7935e93e3a4b25dbe))
- Generate images ([dbcdd3a](dbcdd3a16e724eec68337b35b3c3f5a8b4dabaab))
- Add quality to image generation and update baseline prompts ([9716dc2](9716dc2207c9d370a8b939216d9405f33e568fef))
- Hide wait msg on failure and show an error message instead ([0421424](0421424c5398502fb3ff54ec63cd449a8398ea5a))
- Adjust UI & add FreeText prompt ([4ed2c92](4ed2c921a6ce8dfc6a733e0b637dd21f2071f144))
- Configurable tvs & resource fields ([8e8ad03](8e8ad0387ce20b1ed66921503a958b9d5f046f7d))
- Add setting for base output ([3d88b08](3d88b080e448786e6f13ff2960afff61fdd772ec))
- Updating area name and consistent cross-browser modAI button styling ([711113d](711113dbfe26a1fd1b6b5dbfdb26d8e6c99940d1))
- Add global.base.output ([77e9379](77e93791e4555b3cac6d55cb3744c63686890641))
- Allow override base.output ([093e371](093e371abe8c3d5ebf63cc887ebc0e6496301791))
- Add support for gemini ([dbc3bd0](dbc3bd046ca8eb0943140199b53d7d727ca6dc31))
- Attach modAI on textareas and rte TVs ([259dd34](259dd34fe62a178aca3945934c3d2dfe543a8388))
- Add support for anthropic models (claude) ([3bea523](3bea523aa380995ea7b7f733f0198a570abb3bd0))
- Consolidate api services ([5150c47](5150c471d1557ee8d7a0df42eab5017979a63837))
- Add support for custom api, compatible with openai ([fb04ee6](fb04ee69404f6e139e12fa56185810408f9d5544))
- Add system setting to configure download path for generated images ([83c14b6](83c14b6a10924c64d01f2ae8da50c6899e69b53e))
- Add support for image generation in gemini models ([83492c9](83492c9d31ee93f65681854bd1ac78753331c132))
- Add vision support for gemini ([2747fa7](2747fa7a62447e7677e7c75b19fbbfc2381bb366))
- Allow model/prompt overriding for vision per field ([94a2414](94a2414aab0c33a5d98b0304b6a4533b2da1159e))
- Move calling AI service to the client side ([7ef59bd](7ef59bd2df8bdead0a07021d68e759574c66cca3))

### 🐛 Bug Fixes

- Add missing import ([90a2f66](90a2f66eab46db6b751a9c955d0ae6526fb19d18))
- Update assetsUrl path ([68780fa](68780fa50b99aa46ce89e3bc4696620a63e43c7c))
- Fix saving altTag ([9e62f83](9e62f839b253cb3c374e12f200e9ef146380dd89)), Resolves #1
- Fix name of global base prompt ([a68bf91](a68bf91862e3ac6f9c4e3678b2848f7448577ae4))
- Fix global base prompt in free text ([faaf61d](faaf61dd172b4d8e674dabbc7d24ae8be41a92ac))
- Fix grabbing chatgpt key ([a173a55](a173a552b94a4158c621fa47c2d48a61efbf5d1d))
- Disable timeout when calling prompt processors ([56b33d8](56b33d84f55019c30c0e768124f88fb582181f33))
- Fix return of generateImage from custom chatgpt integration ([8539f35](8539f350a5d0bbd34eeaed9297c40b4fe959d3a4))
- Use getImageFieldSetting when generating an image ([95f5d90](95f5d90c0a46841e4b176218b681f73fcc4b60c2))

### 📚 Documentation

- More descriptions and initial documentation ([3cefccf](3cefccfbdd7c7ab5708858445f15a41e1087f4c8))
- Document base prompt ([e100400](e1004008f861e804cd8ad49e420fcf4e2f1e5c5b))
- Update README.md ([c2e4e17](c2e4e1779e6760039f8c18b07bd5ca29eab504cb))
- Fixing readme typos and clarifications ([758349f](758349f6aae5b850c00e128f3b6dedcd545e0609))
- Streamline README ([deaee2f](deaee2ff4d098514f56676e5f70dbe942140b783))
- Clarify the README.md even more, because I can't stop ([62ae55d](62ae55df40da60e2fe1c73bb6686834cd4c4d745))
- Update instructions for TV handling + default image to wide ([634e8e0](634e8e0692eaff98861b769e4cff8a2055d9be3c))
- Create LICENSE ([5f24386](5f243867c88772e323766a24d05a11d967055b04))
- Move readme ([d65c986](d65c9863c2ac05c8b3bc96651fd693ff10b82517))
- Documentation updates and Settings names ([808b6d4](808b6d43ed0404eb9981879f2ee3e1c4fc074496))
- Multi-model and custom model usage instructions ([97aa5a0](97aa5a0582ec84d7916d917fdf66ce992fa724f1))
- Fix typos in README ([a89293b](a89293b5d67e0e9dbe4280b3fb05217e50add98e))
- Adjust order and add service specific docs ([0700b38](0700b381c5b22bfea211cd58d2d3aae0ea31459c))
- Add Gemini Image + Vision ([709bd5d](709bd5dada62dd6bcd21bb3ba0c9425ad30ba60c))
- Convert readme to writerside ([fb240bd](fb240bda15c5b7b24f86136acf27339ccbbecd58))
- Set docs to build while in private mode ([e2ccdc7](e2ccdc75f7fd9338654392e51775841c4442c4ce))
- Fix link to docs and add theme ([a13cdc0](a13cdc08ab794aed10a753ee37e0ab439b930043))

### 🎨 Styling

- CSS for nav buttons ([e116c94](e116c943150487bb86cb800698ef6dafa898bb4e))
- Make buttons great again ([76a1f04](76a1f04ce75b45521e9ccb14e638696651fa7126))
- Initial tweaks for Image+ alt button (still need 3px or so top margin) ([ed89a1d](ed89a1d39fcbb63f11064427f9f6d2f1dfd7ee92))
- Fix for minor alignment on alt text icon ([1f3afb6](1f3afb67b72a4a3e0934177e2e060261ac42e359))
- Adjust AI button look ([e7e4929](e7e4929afc7d29797c60c5e39c8bf4192953d7ae))
- Increase height of response textarea in the free text prompt window ([c7533da](c7533daf64644220865de926502cbb6e6fa81bc6))
- Visually simplify button borders ([1b48a84](1b48a84ce5b590584e521c0814ed358b03e0e90e))

### ⚙️ Miscellaneous Tasks

- Version bump ([19fcae4](19fcae4af85a7c24e1f4194db859f376e61c9170))
- Add git-cliff config ([74c2b0d](74c2b0dd29bcad577090b753751e593588b0dea0))
- Add lexicons for system settings ([c00c82e](c00c82ee633e79ec2b81e4d77486a933501999d5))
- Clean up IDs in action ([0c35a59](0c35a59b0790a0e44204005f35550aa26b8a8a0a))
- Convert all text to lexicons ([bd45280](bd452805a79aadeb35e17370bb38f1f4f52ad354))
- Ignore config.core.php file ([222aeab](222aeabbb209da9ef8f8e3efdfaff78f1ff8e3a5))
- Changelog, version bump ([e3eb3a2](e3eb3a27ae5c014d2ac1a4278da8126ae6bdf1e2))


