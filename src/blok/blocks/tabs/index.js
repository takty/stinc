/******/ (function() { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/blocks/tabs/index.js":
/*!**********************************!*\
  !*** ./src/blocks/tabs/index.js ***!
  \**********************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_extends__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/extends */ "./node_modules/@babel/runtime/helpers/esm/extends.js");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _style_scss__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./style.scss */ "./src/blocks/tabs/style.scss");
/* harmony import */ var _editor_scss__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./editor.scss */ "./src/blocks/tabs/editor.scss");








const icon_block = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 48 48"
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("path", {
  d: "M39 36a.5.5 0 0 1-.5.5h-29A.5.5 0 0 1 9 36V19H6v17a3.5 3.5 0 0 0 3.5 3.5h29A3.5 3.5 0 0 0 42 36V19h-3ZM34.74 13a.5.5 0 0 1-.45-.28l-1.4-2.79A3.48 3.48 0 0 0 29.76 8h-5.02a3.48 3.48 0 0 0-3.13 1.93l-1.11 2.22-1.1-2.22A3.48 3.48 0 0 0 16.25 8h-5.02A3.48 3.48 0 0 0 8.1 9.93l-1.74 3.48A3.52 3.52 0 0 0 6 14.97V16h3v-1.03a.5.5 0 0 1 .05-.22l1.74-3.47a.5.5 0 0 1 .45-.28h5.02a.5.5 0 0 1 .45.28l2.45 4.9h2.68l2.45-4.9a.5.5 0 0 1 .45-.28h5.02a.5.5 0 0 1 .45.28l1.4 2.79A3.48 3.48 0 0 0 34.74 16H42v-3Z"
}));
const icon_scroll = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 48 48"
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("path", {
  d: "M36.74 11a.5.5 0 0 1-.45-.28l-1.4-2.79A3.48 3.48 0 0 0 31.76 6h-5.02a3.48 3.48 0 0 0-3.13 1.93l-1.11 2.22-1.1-2.22A3.48 3.48 0 0 0 18.25 6h-5.02a3.48 3.48 0 0 0-3.13 1.93l-1.74 3.48A3.52 3.52 0 0 0 8 12.97V14h3v-1.03a.5.5 0 0 1 .05-.22l1.74-3.47a.5.5 0 0 1 .45-.28h5.02a.5.5 0 0 1 .45.28l2.45 4.9h2.68l2.45-4.9a.5.5 0 0 1 .45-.28h5.02a.5.5 0 0 1 .45.28l1.4 2.79A3.48 3.48 0 0 0 36.74 14H40v-3ZM36.29 38.72l-1.4-2.79A3.48 3.48 0 0 0 31.76 34h-5.02a3.48 3.48 0 0 0-3.13 1.93l-1.11 2.22-1.1-2.22A3.48 3.48 0 0 0 18.25 34h-5.02a3.48 3.48 0 0 0-3.13 1.93l-1.74 3.48A3.52 3.52 0 0 0 8 40.97V42h3v-1.03a.5.5 0 0 1 .05-.22l1.74-3.47a.5.5 0 0 1 .45-.28h5.02a.5.5 0 0 1 .45.28l2.45 4.9h2.68l2.45-4.9a.5.5 0 0 1 .45-.28h5.02a.5.5 0 0 1 .45.28l1.4 2.79A3.48 3.48 0 0 0 36.74 42H40v-3h-3.26a.5.5 0 0 1-.45-.28ZM36.5 17h-25A3.5 3.5 0 0 0 8 20.5v7a3.5 3.5 0 0 0 3.5 3.5h25a3.5 3.5 0 0 0 3.5-3.5v-7a3.5 3.5 0 0 0-3.5-3.5Zm.5 10.5a.5.5 0 0 1-.5.5h-25a.5.5 0 0 1-.5-.5v-7a.5.5 0 0 1 .5-.5h25a.5.5 0 0 1 .5.5Z"
}));
const icon_stack = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 48 48"
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("path", {
  d: "M36 32.5v-18a3.5 3.5 0 0 0-3.5-3.5H21.24a.5.5 0 0 1-.45-.28l-1.4-2.79A3.48 3.48 0 0 0 16.26 6h-5.02A3.48 3.48 0 0 0 8.1 7.93l-1.74 3.48A3.52 3.52 0 0 0 6 12.97V32.5A3.5 3.5 0 0 0 9.5 36h23a3.5 3.5 0 0 0 3.5-3.5Zm-27 0V12.97a.5.5 0 0 1 .05-.22l1.74-3.47a.5.5 0 0 1 .45-.28h5.02a.5.5 0 0 1 .45.28l1.4 2.79A3.48 3.48 0 0 0 21.24 14H32.5a.5.5 0 0 1 .5.5v18a.5.5 0 0 1-.5.5h-23a.5.5 0 0 1-.5-.5Z"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("path", {
  d: "M40.5 16v22a2.5 2.5 0 0 1-2.5 2.5H12v3h26a5.5 5.5 0 0 0 5.5-5.5V16Z"
}));

function edit(_ref) {
  let {
    attributes,
    setAttributes
  } = _ref;
  const {
    type
  } = attributes;

  const setType = type => setAttributes({
    type
  });

  const label = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__.__)('scroll' === type ? 'Tabs [Scroll]' : 'Tabs [Stack]', 'wpinc');

  const blockProps = (0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.useBlockProps)();
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("div", (0,_babel_runtime_helpers_extends__WEBPACK_IMPORTED_MODULE_0__["default"])({
    "data-container-label": label
  }, blockProps), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.BlockControls, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.Toolbar, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.ToolbarButton, {
    isActive: 'scroll' === type,
    onClick: () => setType('scroll'),
    icon: icon_scroll,
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__.__)('Scroll', 'wpinc')
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.ToolbarButton, {
    isActive: 'stack' === type,
    onClick: () => setType('stack'),
    icon: icon_stack,
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__.__)('Stack', 'wpinc')
  }))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.InnerBlocks, null));
}

function save(_ref2) {
  let {
    attributes
  } = _ref2;
  const blockProps = _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.useBlockProps.save({
    className: `tab-${attributes.type}`
  });
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("div", blockProps, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.InnerBlocks.Content, null));
}

(0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_5__.registerBlockType)('wpinc/tabs', {
  edit,
  save,
  icon: icon_block
});

/***/ }),

/***/ "./src/blocks/tabs/editor.scss":
/*!*************************************!*\
  !*** ./src/blocks/tabs/editor.scss ***!
  \*************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./src/blocks/tabs/style.scss":
/*!************************************!*\
  !*** ./src/blocks/tabs/style.scss ***!
  \************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "@wordpress/block-editor":
/*!*************************************!*\
  !*** external ["wp","blockEditor"] ***!
  \*************************************/
/***/ (function(module) {

module.exports = window["wp"]["blockEditor"];

/***/ }),

/***/ "@wordpress/blocks":
/*!********************************!*\
  !*** external ["wp","blocks"] ***!
  \********************************/
/***/ (function(module) {

module.exports = window["wp"]["blocks"];

/***/ }),

/***/ "@wordpress/components":
/*!************************************!*\
  !*** external ["wp","components"] ***!
  \************************************/
/***/ (function(module) {

module.exports = window["wp"]["components"];

/***/ }),

/***/ "@wordpress/element":
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
/***/ (function(module) {

module.exports = window["wp"]["element"];

/***/ }),

/***/ "@wordpress/i18n":
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
/***/ (function(module) {

module.exports = window["wp"]["i18n"];

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/extends.js":
/*!************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/extends.js ***!
  \************************************************************/
/***/ (function(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ _extends; }
/* harmony export */ });
function _extends() {
  _extends = Object.assign || function (target) {
    for (var i = 1; i < arguments.length; i++) {
      var source = arguments[i];

      for (var key in source) {
        if (Object.prototype.hasOwnProperty.call(source, key)) {
          target[key] = source[key];
        }
      }
    }

    return target;
  };

  return _extends.apply(this, arguments);
}

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
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/chunk loaded */
/******/ 	!function() {
/******/ 		var deferred = [];
/******/ 		__webpack_require__.O = function(result, chunkIds, fn, priority) {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			var notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				var chunkIds = deferred[i][0];
/******/ 				var fn = deferred[i][1];
/******/ 				var priority = deferred[i][2];
/******/ 				var fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every(function(key) { return __webpack_require__.O[key](chunkIds[j]); })) {
/******/ 						chunkIds.splice(j--, 1);
/******/ 					} else {
/******/ 						fulfilled = false;
/******/ 						if(priority < notFulfilled) notFulfilled = priority;
/******/ 					}
/******/ 				}
/******/ 				if(fulfilled) {
/******/ 					deferred.splice(i--, 1)
/******/ 					var r = fn();
/******/ 					if (r !== undefined) result = r;
/******/ 				}
/******/ 			}
/******/ 			return result;
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	!function() {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = function(module) {
/******/ 			var getter = module && module.__esModule ?
/******/ 				function() { return module['default']; } :
/******/ 				function() { return module; };
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	!function() {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = function(exports, definition) {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	!function() {
/******/ 		__webpack_require__.o = function(obj, prop) { return Object.prototype.hasOwnProperty.call(obj, prop); }
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	!function() {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = function(exports) {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	!function() {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		var installedChunks = {
/******/ 			"index": 0,
/******/ 			"./style-index": 0
/******/ 		};
/******/ 		
/******/ 		// no chunk on demand loading
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		__webpack_require__.O.j = function(chunkId) { return installedChunks[chunkId] === 0; };
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		var webpackJsonpCallback = function(parentChunkLoadingFunction, data) {
/******/ 			var chunkIds = data[0];
/******/ 			var moreModules = data[1];
/******/ 			var runtime = data[2];
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some(function(id) { return installedChunks[id] !== 0; })) {
/******/ 				for(moduleId in moreModules) {
/******/ 					if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 						__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 					}
/******/ 				}
/******/ 				if(runtime) var result = runtime(__webpack_require__);
/******/ 			}
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					installedChunks[chunkId][0]();
/******/ 				}
/******/ 				installedChunks[chunkId] = 0;
/******/ 			}
/******/ 			return __webpack_require__.O(result);
/******/ 		}
/******/ 		
/******/ 		var chunkLoadingGlobal = self["webpackChunkwpinc_blok"] = self["webpackChunkwpinc_blok"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	}();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, ["./style-index"], function() { return __webpack_require__("./src/blocks/tabs/index.js"); })
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;
//# sourceMappingURL=index.js.map