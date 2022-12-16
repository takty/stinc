/******/ (function() { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/blocks/field/index.js":
/*!***********************************!*\
  !*** ./src/blocks/field/index.js ***!
  \***********************************/
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
/* harmony import */ var _style_scss__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./style.scss */ "./src/blocks/field/style.scss");
/* harmony import */ var _editor_scss__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./editor.scss */ "./src/blocks/field/editor.scss");

var _window$wpinc_field_a;

/**
 * Field block
 *
 * @author Takuto Yanagida
 * @version 2022-10-12
 */







const icon = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 48 48"
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("path", {
  d: "M40.5 13.5h-3V9a.5.5 0 0 0-.5-.5H11a.5.5 0 0 0-.5.5v4.5h-3V9A3.5 3.5 0 0 1 11 5.5h26A3.5 3.5 0 0 1 40.5 9v4.5ZM40.5 24.5h-3V20a.5.5 0 0 0-.5-.5H11a.5.5 0 0 0-.5.5v4.5h-3V20a3.5 3.5 0 0 1 3.5-3.5h26a3.5 3.5 0 0 1 3.5 3.5v4.5Z"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "27",
  cy: "30",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "33",
  cy: "30",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "21",
  cy: "30",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "15",
  cy: "30",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "9",
  cy: "30",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "39",
  cy: "30",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "27",
  cy: "41",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "39",
  cy: "35.5",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "9",
  cy: "35.5",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "33",
  cy: "41",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "21",
  cy: "41",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "15",
  cy: "41",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "9",
  cy: "41",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "39",
  cy: "41",
  r: "1.5"
}));
const iconKeys = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 48 48"
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "27",
  cy: "30",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "33",
  cy: "30",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "21",
  cy: "30",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "15",
  cy: "30",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "9",
  cy: "30",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "39",
  cy: "30",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "27",
  cy: "41",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "39",
  cy: "35.5",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "9",
  cy: "35.5",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "33",
  cy: "41",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "21",
  cy: "41",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "15",
  cy: "41",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "9",
  cy: "41",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "39",
  cy: "41",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "27",
  cy: "7",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "33",
  cy: "7",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "21",
  cy: "7",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "15",
  cy: "7",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "9",
  cy: "7",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "39",
  cy: "7",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "27",
  cy: "18",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "39",
  cy: "12.5",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "9",
  cy: "12.5",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "33",
  cy: "18",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "21",
  cy: "18",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "15",
  cy: "18",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "9",
  cy: "18",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "39",
  cy: "18",
  r: "1.5"
}));
const iconKey = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 48 48"
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "18.5",
  cy: "14.5",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "24",
  cy: "13",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "29.5",
  cy: "14.5",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "18.5",
  cy: "33.5",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "24",
  cy: "35",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "29.5",
  cy: "33.5",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "14.5",
  cy: "18.5",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "13",
  cy: "24",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "14.5",
  cy: "29.5",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "33.5",
  cy: "18.5",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "35",
  cy: "24",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "33.5",
  cy: "29.5",
  r: "1.5"
}));
const iconKeySel = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 48 48"
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "18.5",
  cy: "14.5",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "24",
  cy: "13",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "29.5",
  cy: "14.5",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "18.5",
  cy: "33.5",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "24",
  cy: "35",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "29.5",
  cy: "33.5",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "14.5",
  cy: "18.5",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "13",
  cy: "24",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "14.5",
  cy: "29.5",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "33.5",
  cy: "18.5",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "35",
  cy: "24",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "33.5",
  cy: "29.5",
  r: "1.5"
}), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("circle", {
  cx: "24",
  cy: "24",
  r: "6"
}));
const label = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__.__)('Items', 'wpinc');
const es = (_window$wpinc_field_a = window?.wpinc_field_args?.entries) !== null && _window$wpinc_field_a !== void 0 ? _window$wpinc_field_a : [];
function edit(_ref) {
  var _es$filter$0$label;
  let {
    attributes,
    setAttributes
  } = _ref;
  const {
    key
  } = attributes;
  const setKey = key => setAttributes({
    key
  });
  if (!key && es.length) setKey(es[0].key);
  const keyLabel = (_es$filter$0$label = es.filter(e => e.key === key)[0]?.label) !== null && _es$filter$0$label !== void 0 ? _es$filter$0$label : label;
  const blockProps = (0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.useBlockProps)({
    className: `field-${key}`
  });
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("div", (0,_babel_runtime_helpers_extends__WEBPACK_IMPORTED_MODULE_0__["default"])({
    "data-container-label": keyLabel
  }, blockProps), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.BlockControls, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.ToolbarDropdownMenu, {
    icon: iconKeys,
    label: label,
    controls: es.map(e => {
      return {
        icon: e.key === key ? iconKeySel : iconKey,
        title: e.label,
        onClick: () => setKey(e.key)
      };
    })
  })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.InnerBlocks, null));
}
function save() {
  const blockProps = _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.useBlockProps.save();
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.InnerBlocks.Content, blockProps);
}
(0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_5__.registerBlockType)('wpinc/field', {
  edit,
  save,
  icon
});

/***/ }),

/***/ "./src/blocks/field/editor.scss":
/*!**************************************!*\
  !*** ./src/blocks/field/editor.scss ***!
  \**************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./src/blocks/field/style.scss":
/*!*************************************!*\
  !*** ./src/blocks/field/style.scss ***!
  \*************************************/
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
  _extends = Object.assign ? Object.assign.bind() : function (target) {
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
/******/ 			"style-index": 0
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
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, ["style-index"], function() { return __webpack_require__("./src/blocks/field/index.js"); })
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;
//# sourceMappingURL=index.js.map