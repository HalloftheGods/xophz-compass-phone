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
    "revision": "4eadf16e410bf73555ac1149542d876b"
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
    "url": "assets/zap-BN-Y9-2N.js",
    "revision": null
  }, {
    "url": "assets/user-plus-LKn7EVgO.js",
    "revision": null
  }, {
    "url": "assets/useQuestbookStore-jLlCta71.js",
    "revision": null
  }, {
    "url": "assets/useQuestbookClientStore-291Olf4p.js",
    "revision": null
  }, {
    "url": "assets/search-DG3gl4Cb.js",
    "revision": null
  }, {
    "url": "assets/message-square-e8XSI3oV.js",
    "revision": null
  }, {
    "url": "assets/index-CxNsMI3k.js",
    "revision": null
  }, {
    "url": "assets/index-CCNxAYSI.css",
    "revision": null
  }, {
    "url": "assets/inbox-pixmwGo7.js",
    "revision": null
  }, {
    "url": "assets/folder-lock-DuRwjkU_.js",
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
    "url": "assets/credit-card-BCCq-IMW.js",
    "revision": null
  }, {
    "url": "assets/createLucideIcon-CieInEJd.js",
    "revision": null
  }, {
    "url": "assets/clock-C_EQNZn4.js",
    "revision": null
  }, {
    "url": "assets/circle-check-Dq9Rq-_-.js",
    "revision": null
  }, {
    "url": "assets/calendar-DzqJS_LL.js",
    "revision": null
  }, {
    "url": "assets/book-user-DzwGyOAK.js",
    "revision": null
  }, {
    "url": "assets/SmokeBackground-C9uScpjl.js",
    "revision": null
  }, {
    "url": "assets/SmokeBackground-C0OZjQT_.css",
    "revision": null
  }, {
    "url": "assets/QuestbookSettingsView-jbgt23n2.css",
    "revision": null
  }, {
    "url": "assets/QuestbookSettingsView-Db2rXY8j.js",
    "revision": null
  }, {
    "url": "assets/QuestbookPipelineView-EAcSSsYG.css",
    "revision": null
  }, {
    "url": "assets/QuestbookPipelineView-DlgABO1f.js",
    "revision": null
  }, {
    "url": "assets/QuestbookOnboardView-b0iJmk01.css",
    "revision": null
  }, {
    "url": "assets/QuestbookOnboardView-D5ad9k_q.js",
    "revision": null
  }, {
    "url": "assets/QuestbookInboxView-CbAUomJR.css",
    "revision": null
  }, {
    "url": "assets/QuestbookInboxView-C5J_M0Nb.js",
    "revision": null
  }, {
    "url": "assets/QuestbookDirectoryView-D4AypvTq.js",
    "revision": null
  }, {
    "url": "assets/QuestbookDirectoryView-BZmyiEZX.css",
    "revision": null
  }, {
    "url": "assets/QuestbookCalendarView-D9SaP0w1.css",
    "revision": null
  }, {
    "url": "assets/QuestbookCalendarView-D5KHRsdP.js",
    "revision": null
  }, {
    "url": "assets/QuestbookActivityView-CpbYwO7r.js",
    "revision": null
  }, {
    "url": "assets/QuestbookActivityView-BMi_lZwG.css",
    "revision": null
  }, {
    "url": "assets/PricingView-ljX0wDsJ.js",
    "revision": null
  }, {
    "url": "assets/PricingView-B-I23q2H.css",
    "revision": null
  }, {
    "url": "assets/PresentationView-DnOThRFq.js",
    "revision": null
  }, {
    "url": "assets/PresentationView-DAndfkfF.css",
    "revision": null
  }, {
    "url": "assets/ClientVaultView-Dm4o2Pf5.js",
    "revision": null
  }, {
    "url": "assets/ClientTasksView-D20vEXKn.css",
    "revision": null
  }, {
    "url": "assets/ClientTasksView-Ci31Li_M.js",
    "revision": null
  }, {
    "url": "assets/ClientMessagesView-BC5_NuDv.js",
    "revision": null
  }, {
    "url": "assets/ClientBillingView-G7fvLlXa.js",
    "revision": null
  }, {
    "url": "assets/BazaarProductsView-g3861Lb3.js",
    "revision": null
  }, {
    "url": "assets/BazaarProductsView-KFegLKiT.css",
    "revision": null
  }, {
    "url": "assets/BazaarProcurementView-tSWx5m_z.css",
    "revision": null
  }, {
    "url": "assets/BazaarProcurementView-BEkRDGdN.js",
    "revision": null
  }, {
    "url": "assets/BazaarPosView-DbW7HAMo.js",
    "revision": null
  }, {
    "url": "assets/BazaarPosView-BywpnqlM.css",
    "revision": null
  }, {
    "url": "assets/BazaarOrdersView-DjRTqT4q.css",
    "revision": null
  }, {
    "url": "assets/BazaarOrdersView-D2iGTNfc.js",
    "revision": null
  }, {
    "url": "assets/BazaarDashView-lscdXiPK.css",
    "revision": null
  }, {
    "url": "assets/BazaarDashView-NMS8L-ha.js",
    "revision": null
  }, {
    "url": "manifest.webmanifest",
    "revision": "21f7b7f178811da730d183256b37b549"
  }], {});
  workbox.cleanupOutdatedCaches();

}));
