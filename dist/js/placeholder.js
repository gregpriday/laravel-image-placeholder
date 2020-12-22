/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/js/placeholder.js":
/*!*************************************!*\
  !*** ./resources/js/placeholder.js ***!
  \*************************************/
/*! exports provided: displayPlaceholders */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "displayPlaceholders", function() { return displayPlaceholders; });
/**
 * Create the SVG for this
 * @param image
 * @returns {string}
 */
function getImageSvg(image) {
  var imageWidth = parseInt(image.getAttribute('width'));
  var imageHeight = parseInt(image.getAttribute('height'));
  var imageData = image.getAttribute('data-placeholder'); // Lets start drawing the canvas

  var svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' + imageWidth + ' ' + imageHeight + '" width="' + imageWidth + '" height="' + imageHeight + '">'; // Add the filter definition by Taylor Hunt - https://codepen.io/tigt/post/fixing-the-white-glow-in-the-css-blur-filter

  var blurRadius = Math.floor(imageWidth > imageHeight ? imageWidth / 32 : imageHeight / 32);
  svg += '<filter id="better-blur" x="0" y="0" width="1" height="1"><feGaussianBlur stdDeviation="' + blurRadius + '" result="blurred"/><feMorphology in="blurred" operator="dilate" radius="' + blurRadius + '" result="expanded"/><feMerge><feMergeNode in="expanded"/><feMergeNode in="blurred"/></feMerge></filter>';
  svg += '<g id="voronoi" filter="url(#better-blur)">';
  svg += '<image href="data:image/png;base64,' + imageData + '" width="' + imageWidth + '" height="' + imageHeight + '" />';
  svg += '</g></svg>';
  return svg;
}
/**
 * Main function to display placeholders
 */


function displayPlaceholders() {
  var images = document.querySelectorAll('img[data-placeholder]');

  for (var i = 0; i < images.length; i++) {
    var image = images[i];
    if (!image.getAttribute('data-placeholder')) continue; // Add in the background

    image.style.backgroundPosition = 'center center';
    image.style.backgroundSize = 'cover';
    image.style.backgroundImage = "url('data:image/svg+xml;base64," + btoa(getImageSvg(image)) + "')";
    image.removeAttribute('data-placeholder');
  }
}

/***/ }),

/***/ 0:
/*!*******************************************!*\
  !*** multi ./resources/js/placeholder.js ***!
  \*******************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! /Users/gpriday/Dropbox/Sites/postcontent/packages/voronoi-placeholder/resources/js/placeholder.js */"./resources/js/placeholder.js");


/***/ })

/******/ });