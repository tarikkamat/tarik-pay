/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/checkout.js":
/*!*************************!*\
  !*** ./src/checkout.js ***!
  \*************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _wordpress_html_entities__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/html-entities */ "@wordpress/html-entities");
/* harmony import */ var _wordpress_html_entities__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_html_entities__WEBPACK_IMPORTED_MODULE_0__);

var Content = function Content() {
  return (0,_wordpress_html_entities__WEBPACK_IMPORTED_MODULE_0__.decodeEntities)(settings.description);
};
var Label = function Label() {
  return /*#__PURE__*/React.createElement("span", {
    style: {
      width: '100%'
    }
  }, label, /*#__PURE__*/React.createElement(Icon, null));
};
var Icon = function Icon() {
  return settings.icon ? /*#__PURE__*/React.createElement("img", {
    src: settings.icon,
    style: {
      "float": 'right',
      marginRight: '20px'
    }
  }) : '';
};
var getSetting = window.wc.wcSettings.getSetting;
var settings = getSetting('iyzico_data', {});
var label = (0,_wordpress_html_entities__WEBPACK_IMPORTED_MODULE_0__.decodeEntities)(settings.title);
var CheckoutOptions = {
  name: "iyzico",
  label: /*#__PURE__*/React.createElement(Label, null),
  content: /*#__PURE__*/React.createElement(Content, null),
  edit: /*#__PURE__*/React.createElement(Content, null),
  canMakePayment: function canMakePayment() {
    return true;
  },
  ariaLabel: label,
  supports: {
    features: settings.supports
  }
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (CheckoutOptions);

/***/ }),

/***/ "./src/pwi.js":
/*!********************!*\
  !*** ./src/pwi.js ***!
  \********************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _wordpress_html_entities__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/html-entities */ "@wordpress/html-entities");
/* harmony import */ var _wordpress_html_entities__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_html_entities__WEBPACK_IMPORTED_MODULE_0__);

var Content = function Content() {
  return (0,_wordpress_html_entities__WEBPACK_IMPORTED_MODULE_0__.decodeEntities)(settings.description);
};
var Label = function Label() {
  return /*#__PURE__*/React.createElement("span", {
    style: {
      width: '100%'
    }
  }, label, /*#__PURE__*/React.createElement(Icon, null));
};
var Icon = function Icon() {
  return settings.icon ? /*#__PURE__*/React.createElement("img", {
    src: settings.icon,
    style: {
      "float": 'right',
      marginRight: '20px'
    }
  }) : '';
};
var getSetting = window.wc.wcSettings.getSetting;
var settings = getSetting('pwi_data', {});
var label = (0,_wordpress_html_entities__WEBPACK_IMPORTED_MODULE_0__.decodeEntities)(settings.title);
var PwiOptions = {
  name: "pwi",
  label: /*#__PURE__*/React.createElement(Label, null),
  content: /*#__PURE__*/React.createElement(Content, null),
  edit: /*#__PURE__*/React.createElement(Content, null),
  canMakePayment: function canMakePayment() {
    return true;
  },
  ariaLabel: label,
  supports: {
    features: settings.supports
  }
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (PwiOptions);

/***/ }),

/***/ "@wordpress/html-entities":
/*!**************************************!*\
  !*** external ["wp","htmlEntities"] ***!
  \**************************************/
/***/ ((module) => {

module.exports = window["wp"]["htmlEntities"];

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
(() => {
/*!**********************!*\
  !*** ./src/index.js ***!
  \**********************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _pwi__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./pwi */ "./src/pwi.js");
/* harmony import */ var _checkout__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./checkout */ "./src/checkout.js");


var registerPaymentMethod = window.wc.wcBlocksRegistry.registerPaymentMethod;
if (_pwi__WEBPACK_IMPORTED_MODULE_0__["default"].ariaLabel !== undefined) {
  registerPaymentMethod(_pwi__WEBPACK_IMPORTED_MODULE_0__["default"]);
}
if (_checkout__WEBPACK_IMPORTED_MODULE_1__["default"].ariaLabel !== undefined) {
  registerPaymentMethod(_checkout__WEBPACK_IMPORTED_MODULE_1__["default"]);
}
})();

/******/ })()
;
//# sourceMappingURL=blocks.js.map