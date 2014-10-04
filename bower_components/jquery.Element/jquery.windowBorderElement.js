////////////////////////////////////////////
//
// Отображение элемента у границы окна браузера в пределах размера родительского элемента
// Пытаемся разместить элемент в середине экрана
//
// Разработчик Дмитрий Протопопов http://protopopov.ru
// RBA DESIGN INTERNATIONAL LLC http://rbadesign.us
//
// Используются нижеследующие компоненты:
// jQuery http://jquery.com
// cssParentSelector https://github.com/Idered/cssParentSelector
/*
Порядок использования:
1. Установить у нужного элемента класс "left-border-element"
2. Или установить у нужного элемента класс "right-border-element"
3. Или установить у нужного элемента класс "top-border-element"
4. Или установить у нужного элемента класс "bottom-border-element"

Пример:
	<script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
    <script src="jQuery.cssParentSelector.js"></script>
	<script src="jquery.windowBorderElement.js"></script>
	<link href="jquery.windowBorderElement.css" rel="stylesheet" type="text/css" />
	
<div class="left-border-element">
Hello world
</div>
*/
////////////////////////////////////////////
(function($) {
    // This method is a JavaScript extension to the ECMA-262 standard; as such it may not be present in other 
    // implementations of the standard. To make it work you need to add following code at the top of your script:
    if (!Array.prototype.forEach) {
        Array.prototype.forEach = function(fun /*,thisp*/) {
            var len = this.length;
            if (typeof fun != "function")
                throw new TypeError();

            var thisp = arguments[1];
            for (var i = 0; i < len; i++) {
                if (i in this)
                    fun.call(thisp, this[i], i, this);
            }
        };
    }
    ////////////////////////////////////////////
    /**
	 * Shim for "fixing" IE's lack of support (IE < 9) for applying slice
	 * on host objects like NamedNodeMap, NodeList, and HTMLCollection
	 * (technically, since host objects have been implementation-dependent,
	 * at least before ES6, IE hasn't needed to work this way).
	 * Also works on strings, fixes IE < 9 to allow an explicit undefined
	 * for the 2nd argument (as in Firefox), and prevents errors when 
	 * called on other DOM objects.
	*/
    (function() {
        'use strict';
        var _slice = Array.prototype.slice;

        try {
            // Can't be used with DOM elements in IE < 9
            _slice.call(document.documentElement);
        } catch (e) { // Fails in IE < 9
            Array.prototype.slice = function(begin, end) {
                var i, arrl = this.length, a = [];
                // Although IE < 9 does not fail when applying Array.prototype.slice
                // to strings, here we do have to duck-type to avoid failing
                // with IE < 9's lack of support for string indexes
                if (this.charAt) {
                    for (i = 0; i < arrl; i++) {
                        a.push(this.charAt(i));
                    }
                }
                // This will work for genuine arrays, array-like objects, 
                // NamedNodeMap (attributes, entities, notations),
                // NodeList (e.g., getElementsByTagName), HTMLCollection (e.g., childNodes),
                // and will not fail on other DOM objects (as do DOM elements in IE < 9)
                else {
                    // IE < 9 (at least IE < 9 mode in IE 10) does not work with
                    // node.attributes (NamedNodeMap) without a dynamically checked length here
                    for (i = 0; i < this.length; i++) {
                        a.push(this[i]);
                    }
                }
                // IE < 9 gives errors here if end is allowed as undefined
                // (as opposed to just missing) so we default ourselves
                return _slice.call(a, begin, end || a.length);
            };
        }
    }());
    $.windowBorders = ["left", "right", "top", "bottom"];
    $.windowBorders.forEach(function(item) {
        $[item + "BorderElement"] = "." + item + "-border-element";
    });
    var borderElement = {
        top: function(element, wrapper, wrapperOffset) {
            return wrapperOffset.top - window.pageYOffset;
        },
        left: function(element, wrapper, wrapperOffset) {
            return wrapperOffset.left - window.pageXOffset;
        },
        right: function(element, wrapper, wrapperOffset) {
            return borderElement.left(element, wrapper, wrapperOffset) + $(wrapper).width();
        },
        bottom: function(element, wrapper, wrapperOffset) {
            return borderElement.top(element, wrapper, wrapperOffset) + $(wrapper).height();
        },
        foo: {
            top: function(element, wrapper, wrapperOffset) {
                var top0 = borderElement.top(element, wrapper, wrapperOffset);
                var start = Math.max(0, top0);
                var end = Math.min($(window).height(), borderElement.bottom(element, wrapper, wrapperOffset));
                var top = ($(window).height() - $(element).height()) / 2;
                top = Math.max(top, start);
                top = Math.min(top, end - $(element).height());
                top -= top0;
                top = Math.min(top, $(wrapper).height() - $(element).height());
                top = Math.max(top, 0);
                $(element).css("top", top + "px");
            },
            left: function(element, wrapper, wrapperOffset) {
                var left0 = borderElement.left(element, wrapper, wrapperOffset);
                var start = Math.max(0, left0);
                var end = Math.min($(window).width(), borderElement.right(element, wrapper, wrapperOffset));
                var left = ($(window).width() - $(element).width()) / 2;
                left = Math.max(left, start);
                left = Math.min(left, end - $(element).width());
                left -= left0;
                left = Math.min(left, $(wrapper).width() - $(element).width());
                left = Math.max(left, 0);
                $(element).css("left", left + "px");
            }
        },
        bar: {
            left: function(element, wrapper, wrapperOffset) {
                $(element).css("left", (0 - borderElement.left(element, wrapper, wrapperOffset)) + "px");
            },
            right: function(element, wrapper, wrapperOffset) {
                $(element).css("right", (borderElement.right(element, wrapper, wrapperOffset) - $(window).width()) + "px");
            },
            top: function(element, wrapper, wrapperOffset) {
                $(element).css("top", (0 - borderElement.top(element, wrapper, wrapperOffset)) + "px");
            },
            bottom: function(element, wrapper, wrapperOffset) {
                $(element).css("bottom", (borderElement.bottom(element, wrapper, wrapperOffset) - $(window).height()) + "px");
            }
        }
    };
    var initWindowBorderElement = function() {
        $.windowBorders.slice(0, 2).forEach(function(item) {
            $($[item + "BorderElement"]).data("foo", borderElement.foo.top);
        });
        $.windowBorders.slice(2).forEach(function(item) {
            $($[item + "BorderElement"]).data("foo", borderElement.foo.left);
        });
        $.windowBorders.forEach(function(item) {
            $($[item + "BorderElement"]).data("bar", borderElement.bar[item]);
        });
        $.windowBorders.forEach(function(item) {
            $($[item + "BorderElement"]).css("position", "absolute");
            $($[item + "BorderElement"]).css("display", "inline-block");
        });
        $.windowBorders.forEach(function(item) {
            $($[item + "BorderElement"]).each(function(index, element) {
                var wrapper = $(element).parent();
                if ($(wrapper).css("position") == "static") $(wrapper).css("position", "relative");
            });
        });
        $.windowBorders.forEach(function(item) {
            $($[item + "BorderElement"]).resize(function() {
                var wrapper = $(this).parent();
                var wrapperOffset = $(wrapper).offset();
                $(this).data("foo")(this, wrapper, wrapperOffset);
                $(this).data("bar")(this, wrapper, wrapperOffset);
            });
        });
    };
    $(document).ready(initWindowBorderElement);
    var updateWindowBorderElement = function() {
        $.windowBorders.forEach(function(item) {
            $($[item + "BorderElement"]).each(function(index, element) {
                var wrapper = $(element).parent();
                var wrapperOffset = $(wrapper).offset();
                $(element).data("foo")(element, wrapper, wrapperOffset);
                $(element).data("bar")(element, wrapper, wrapperOffset);
            });
        });
    };
    $(document).ready(updateWindowBorderElement);
    $(document).resize(updateWindowBorderElement);
    $(window).resize(updateWindowBorderElement);
    $(window).scroll(updateWindowBorderElement);
    $(document).ajaxComplete(updateWindowBorderElement);
    $(document).ajaxStop(updateWindowBorderElement);
})(jQuery);