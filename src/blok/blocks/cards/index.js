/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/blocks/cards/index.js":
/*!***********************************!*\
  !*** ./src/blocks/cards/index.js ***!
  \***********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

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
/* harmony import */ var _style_scss__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./style.scss */ "./src/blocks/cards/style.scss");
/* harmony import */ var _editor_scss__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./editor.scss */ "./src/blocks/cards/editor.scss");



function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it.return != null) it.return(); } finally { if (didErr) throw err; } } }; }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

/**
 * Cards block
 *
 * @author Takuto Yanagida
 * @version 2022-03-24
 */






var icon = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 48 48"
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("path", {
  d: "M18.5 22h-7A3.5 3.5 0 0 1 8 18.5v-7A3.5 3.5 0 0 1 11.5 8h7a3.5 3.5 0 0 1 3.5 3.5v7a3.5 3.5 0 0 1-3.5 3.5Zm-7-11a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 .5-.5v-7a.5.5 0 0 0-.5-.5ZM18.5 40h-7A3.5 3.5 0 0 1 8 36.5v-7a3.5 3.5 0 0 1 3.5-3.5h7a3.5 3.5 0 0 1 3.5 3.5v7a3.5 3.5 0 0 1-3.5 3.5Zm-7-11a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 .5-.5v-7a.5.5 0 0 0-.5-.5ZM36.5 22h-7a3.5 3.5 0 0 1-3.5-3.5v-7A3.5 3.5 0 0 1 29.5 8h7a3.5 3.5 0 0 1 3.5 3.5v7a3.5 3.5 0 0 1-3.5 3.5Zm-7-11a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 .5-.5v-7a.5.5 0 0 0-.5-.5Z"
}));
var icon_card_2 = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 48 48"
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("path", {
  d: "M38.5 6h-29A3.5 3.5 0 0 0 6 9.5v29A3.5 3.5 0 0 0 9.5 42h29a3.5 3.5 0 0 0 3.5-3.5v-29A3.5 3.5 0 0 0 38.5 6Zm-29 33a.5.5 0 0 1-.5-.5v-29a.5.5 0 0 1 .5-.5h13v30Zm29.5-.5a.5.5 0 0 1-.5.5h-13V9h13a.5.5 0 0 1 .5.5Z"
}));
var icon_card_3 = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 48 48"
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("path", {
  d: "M38.5 12h-29A3.5 3.5 0 0 0 6 15.5v17A3.5 3.5 0 0 0 9.5 36h29a3.5 3.5 0 0 0 3.5-3.5v-17a3.5 3.5 0 0 0-3.5-3.5Zm-29 21a.5.5 0 0 1-.5-.5v-17a.5.5 0 0 1 .5-.5H17v18ZM20 33V15h8v18Zm19-.5a.5.5 0 0 1-.5.5H31V15h7.5a.5.5 0 0 1 .5.5Z"
}));
var icon_card_4 = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 48 48"
}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("path", {
  d: "M38.5 15h-29A3.5 3.5 0 0 0 6 18.5v11A3.5 3.5 0 0 0 9.5 33h29a3.5 3.5 0 0 0 3.5-3.5v-11a3.5 3.5 0 0 0-3.5-3.5Zm-29 15a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H14v12Zm7.5 0V18h5.5v12Zm8.5 0V18H31v12Zm13.5-.5a.5.5 0 0 1-.5.5H34V18h4.5a.5.5 0 0 1 .5.5Z"
}));

function edit(_ref) {
  var attributes = _ref.attributes,
      setAttributes = _ref.setAttributes;
  var number = attributes.number;

  var setNumber = function setNumber(number) {
    return setAttributes({
      number: number
    });
  };

  var label = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__.__)("Cards [".concat(number, " Columns]"), 'wpinc');

  var blockProps = (0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.useBlockProps)({
    className: "card-".concat(number)
  });
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("div", (0,_babel_runtime_helpers_extends__WEBPACK_IMPORTED_MODULE_0__["default"])({
    "data-container-label": label
  }, blockProps), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.BlockControls, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.Toolbar, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.ToolbarButton, {
    isActive: '2' === number,
    onClick: function onClick() {
      return setNumber('2');
    },
    icon: icon_card_2,
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__.__)('2 Columns', 'wpinc')
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.ToolbarButton, {
    isActive: '3' === number,
    onClick: function onClick() {
      return setNumber('3');
    },
    icon: icon_card_3,
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__.__)('3 Columns', 'wpinc')
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.ToolbarButton, {
    isActive: '4' === number,
    onClick: function onClick() {
      return setNumber('4');
    },
    icon: icon_card_4,
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__.__)('4 Columns', 'wpinc')
  }))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.InnerBlocks, {
    allowedBlocks: ['wpinc/card'],
    orientation: "horizontal"
  }));
}

function save(_ref2) {
  var _window$wpinc_cards_a, _window, _window$wpinc_cards_a2;

  var attributes = _ref2.attributes;
  var cls = (_window$wpinc_cards_a = (_window = window) === null || _window === void 0 ? void 0 : (_window$wpinc_cards_a2 = _window.wpinc_cards_args) === null || _window$wpinc_cards_a2 === void 0 ? void 0 : _window$wpinc_cards_a2.class_card) !== null && _window$wpinc_cards_a !== void 0 ? _window$wpinc_cards_a : 'card-%d';
  var blockProps = _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.useBlockProps.save({
    className: cls.replaceAll('%d', attributes.number)
  });
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)("div", blockProps, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.InnerBlocks.Content, null));
}

var transforms = {
  from: [{
    type: 'raw',
    selector: 'div.column-2, div.column-3, div.column-4',
    transform: function transform(node) {
      var cards = [];
      var ds = node.querySelectorAll(':scope > div');

      var _iterator = _createForOfIteratorHelper(ds),
          _step;

      try {
        for (_iterator.s(); !(_step = _iterator.n()).done;) {
          var d = _step.value;
          cards.push((0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_5__.createBlock)('wpinc/card', {}, (0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_5__.rawHandler)({
            HTML: d.innerHTML
          })));
        }
      } catch (err) {
        _iterator.e(err);
      } finally {
        _iterator.f();
      }

      var number = 2;
      if (node.classList.contains('column-3')) number = 3;
      if (node.classList.contains('column-4')) number = 4;
      return (0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_5__.createBlock)('wpinc/cards', {
        number: number
      }, cards);
    }
  }]
};
(0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_5__.registerBlockType)('wpinc/cards', {
  edit: edit,
  save: save,
  icon: icon,
  transforms: transforms
});

/***/ }),

/***/ "./src/blocks/cards/editor.scss":
/*!**************************************!*\
  !*** ./src/blocks/cards/editor.scss ***!
  \**************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./src/blocks/cards/style.scss":
/*!*************************************!*\
  !*** ./src/blocks/cards/style.scss ***!
  \*************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "@wordpress/block-editor":
/*!*************************************!*\
  !*** external ["wp","blockEditor"] ***!
  \*************************************/
/***/ ((module) => {

module.exports = window["wp"]["blockEditor"];

/***/ }),

/***/ "@wordpress/blocks":
/*!********************************!*\
  !*** external ["wp","blocks"] ***!
  \********************************/
/***/ ((module) => {

module.exports = window["wp"]["blocks"];

/***/ }),

/***/ "@wordpress/components":
/*!************************************!*\
  !*** external ["wp","components"] ***!
  \************************************/
/***/ ((module) => {

module.exports = window["wp"]["components"];

/***/ }),

/***/ "@wordpress/element":
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
/***/ ((module) => {

module.exports = window["wp"]["element"];

/***/ }),

/***/ "@wordpress/i18n":
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
/***/ ((module) => {

module.exports = window["wp"]["i18n"];

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/extends.js":
/*!************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/extends.js ***!
  \************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _extends)
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
/******/ 	(() => {
/******/ 		var deferred = [];
/******/ 		__webpack_require__.O = (result, chunkIds, fn, priority) => {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			var notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				var [chunkIds, fn, priority] = deferred[i];
/******/ 				var fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every((key) => (__webpack_require__.O[key](chunkIds[j])))) {
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
/******/ 	})();
/******/ 	
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
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	(() => {
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
/******/ 		__webpack_require__.O.j = (chunkId) => (installedChunks[chunkId] === 0);
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		var webpackJsonpCallback = (parentChunkLoadingFunction, data) => {
/******/ 			var [chunkIds, moreModules, runtime] = data;
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some((id) => (installedChunks[id] !== 0))) {
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
/******/ 		var chunkLoadingGlobal = globalThis["webpackChunkwpinc_blok"] = globalThis["webpackChunkwpinc_blok"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, ["style-index"], () => (__webpack_require__("./src/blocks/cards/index.js")))
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;
//# sourceMappingURL=index.js.map