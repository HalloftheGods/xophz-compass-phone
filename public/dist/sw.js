/**
 * Copyright 2018 Google Inc. All Rights Reserved.
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *     http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

// If the loader is already loaded, just stop.
if (!self.define) {
  let registry = {};

  // Used for `eval` and `importScripts` where we can't get script URL by other means.
  // In both cases, it's safe to use a global var because those functions are synchronous.
  let nextDefineUri;

  const singleRequire = (uri, parentUri) => {
    uri = new URL(uri + ".js", parentUri).href;
    return registry[uri] || (
      
        new Promise(resolve => {
          if ("document" in self) {
            const script = document.createElement("script");
            script.src = uri;
            script.onload = resolve;
            document.head.appendChild(script);
          } else {
            nextDefineUri = uri;
            importScripts(uri);
            resolve();
          }
        })
      
      .then(() => {
        let promise = registry[uri];
        if (!promise) {
          throw new Error(`Module ${uri} didn’t register its module`);
        }
        return promise;
      })
    );
  };

  self.define = (depsNames, factory) => {
    const uri = nextDefineUri || ("document" in self ? document.currentScript.src : "") || location.href;
    if (registry[uri]) {
      // Module is already loading or loaded.
      return;
    }
    let exports = {};
    const require = depUri => singleRequire(depUri, uri);
    const specialDeps = {
      module: { uri },
      exports,
      require
    };
    registry[uri] = Promise.all(depsNames.map(
      depName => specialDeps[depName] || require(depName)
    )).then(deps => {
      factory(...deps);
      return exports;
    });
  };
}
define(['./workbox-619edae6'], (function (workbox) { 'use strict';

  self.skipWaiting();
  workbox.clientsClaim();
  /**
   * The precacheAndRoute() method efficiently caches and responds to
   * requests for URLs in the manifest.
   * See https://goo.gl/S9QRab
   */
  workbox.precacheAndRoute([{
    "url": "registerSW.js",
    "revision": "402b66900e731ca748771b6fc5e7a068"
  }, {
    "url": "webfonts/fa-solid-900.woff2",
    "revision": "dbf1fc91f1beec2915123257ea4d58ef"
  }, {
    "url": "webfonts/fa-solid-900.svg",
    "revision": "601eb47a1dd75cb133dd8be8f8e5510f"
  }, {
    "url": "webfonts/fa-regular-400.woff2",
    "revision": "a3d7d331957546ae10ad69bb44b83a04"
  }, {
    "url": "webfonts/fa-regular-400.svg",
    "revision": "e70221c01393a280c552b46acd239071"
  }, {
    "url": "webfonts/fa-light-300.woff2",
    "revision": "b33449667ce61388905a97b13f01ea16"
  }, {
    "url": "webfonts/fa-light-300.svg",
    "revision": "b3d910b716a9ddb821eeaf5303b2dc1f"
  }, {
    "url": "webfonts/fa-duotone-900.woff2",
    "revision": "923bc494d832c471ee7b45ba38205fb9"
  }, {
    "url": "webfonts/fa-duotone-900.svg",
    "revision": "314e2724f029624a7167d3ef5198214a"
  }, {
    "url": "webfonts/fa-brands-400.woff2",
    "revision": "f4120760fb40152d1bdb109103063c13"
  }, {
    "url": "webfonts/fa-brands-400.svg",
    "revision": "5bfa00172e97473860a96f18b340f3f5"
  }, {
    "url": "assets/user-plus-52oOTpn6.js",
    "revision": null
  }, {
    "url": "assets/useQuestbookStore-Co5NzVBt.js",
    "revision": null
  }, {
    "url": "assets/useQuestbookClientStore-CFn-v3GH.js",
    "revision": null
  }, {
    "url": "assets/settings-DYuJ15w1.js",
    "revision": null
  }, {
    "url": "assets/search-CIcmwhUI.js",
    "revision": null
  }, {
    "url": "assets/message-square-DeoeW60f.js",
    "revision": null
  }, {
    "url": "assets/mail-C05wOsFp.js",
    "revision": null
  }, {
    "url": "assets/index-D_eGjFkz.js",
    "revision": null
  }, {
    "url": "assets/index-BxrUrvxN.css",
    "revision": null
  }, {
    "url": "assets/inbox-BTTHsqq-.js",
    "revision": null
  }, {
    "url": "assets/folder-lock-CJqsRH3q.js",
    "revision": null
  }, {
    "url": "assets/fa-solid-900-zJJCaBLX.svg",
    "revision": null
  }, {
    "url": "assets/fa-solid-900-CYmgTyZd.woff2",
    "revision": null
  }, {
    "url": "assets/fa-regular-400-D7GIZYaW.svg",
    "revision": null
  }, {
    "url": "assets/fa-regular-400-BMnJ9gFZ.woff2",
    "revision": null
  }, {
    "url": "assets/fa-light-300-JjeHsR9Q.woff2",
    "revision": null
  }, {
    "url": "assets/fa-light-300-DPjzRXa1.svg",
    "revision": null
  }, {
    "url": "assets/fa-duotone-900-kg1a_TEX.svg",
    "revision": null
  }, {
    "url": "assets/fa-duotone-900-ePr-_0-U.woff2",
    "revision": null
  }, {
    "url": "assets/fa-brands-400-CUMtpxsL.svg",
    "revision": null
  }, {
    "url": "assets/fa-brands-400-BoGazL5X.woff2",
    "revision": null
  }, {
    "url": "assets/credit-card-DZPlvY8O.js",
    "revision": null
  }, {
    "url": "assets/createLucideIcon-D7fsTy5f.js",
    "revision": null
  }, {
    "url": "assets/clock-CEG-SrU0.js",
    "revision": null
  }, {
    "url": "assets/circle-check-fwDu_h_Y.js",
    "revision": null
  }, {
    "url": "assets/calendar-DfE1_8gl.js",
    "revision": null
  }, {
    "url": "assets/book-user-C4uSWcrw.js",
    "revision": null
  }, {
    "url": "assets/SmokeBackground-C0OZjQT_.css",
    "revision": null
  }, {
    "url": "assets/SmokeBackground-B8YULX_i.js",
    "revision": null
  }, {
    "url": "assets/QuestbookSettingsView-jbgt23n2.css",
    "revision": null
  }, {
    "url": "assets/QuestbookSettingsView-D8mmnwgV.js",
    "revision": null
  }, {
    "url": "assets/QuestbookPipelineView-EAcSSsYG.css",
    "revision": null
  }, {
    "url": "assets/QuestbookPipelineView-CUEFEMAr.js",
    "revision": null
  }, {
    "url": "assets/QuestbookOnboardView-b0iJmk01.css",
    "revision": null
  }, {
    "url": "assets/QuestbookOnboardView-CaCIfYtC.js",
    "revision": null
  }, {
    "url": "assets/QuestbookInboxView-DbA66udj.js",
    "revision": null
  }, {
    "url": "assets/QuestbookInboxView-CbAUomJR.css",
    "revision": null
  }, {
    "url": "assets/QuestbookDirectoryView-l2_h6fVp.js",
    "revision": null
  }, {
    "url": "assets/QuestbookDirectoryView-BZmyiEZX.css",
    "revision": null
  }, {
    "url": "assets/QuestbookCalendarView-D9SaP0w1.css",
    "revision": null
  }, {
    "url": "assets/QuestbookCalendarView-CqeD64l_.js",
    "revision": null
  }, {
    "url": "assets/QuestbookActivityView-DCugymXp.js",
    "revision": null
  }, {
    "url": "assets/QuestbookActivityView-BMi_lZwG.css",
    "revision": null
  }, {
    "url": "assets/PricingView-Cl2oDWaQ.css",
    "revision": null
  }, {
    "url": "assets/PricingView-BFiIFkgt.js",
    "revision": null
  }, {
    "url": "assets/PresentationView-j57U4eEl.css",
    "revision": null
  }, {
    "url": "assets/PresentationView-Cz481fec.js",
    "revision": null
  }, {
    "url": "assets/ClientVaultView-C-z-oLY7.js",
    "revision": null
  }, {
    "url": "assets/ClientTasksView-DYuy0Xh0.js",
    "revision": null
  }, {
    "url": "assets/ClientTasksView-D20vEXKn.css",
    "revision": null
  }, {
    "url": "assets/ClientMessagesView-DHMfh4-L.js",
    "revision": null
  }, {
    "url": "assets/ClientBillingView-n7uG9FE2.js",
    "revision": null
  }, {
    "url": "manifest.webmanifest",
    "revision": "96735d7c4463898148040222e3c676ac"
  }], {});
  workbox.cleanupOutdatedCaches();

}));
