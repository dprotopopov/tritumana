////////////////////////////////////////////
//
// Центровка содерхимого элемента по горизонтали и вертикали
//
// Разработчик Дмитрий Протопопов http://protopopov.ru
// RBA DESIGN INTERNATIONAL LLC http://rbadesign.us
//
// Используются нижеследующие компоненты:
// jQuery http://jquery.com
// cssParentSelector https://github.com/Idered/cssParentSelector
/*
Порядок использования:
1. Установить у нужного элемента класс "center-element-content"
Если к данному элементу применяется CSS с опцией position, то изменить опцию position на !important

Пример:
	<script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
	<script src="jquery.centerElementContent.js"></script>
	<link href="jquery.centerElementContent.css" rel="stylesheet" type="text/css" />
	
<div class="center-element-content">
<p>Row1</p>
<p>Row2</p>
</div>
*/
////////////////////////////////////////////
(function($) {
    $.fullScreenElement = '.full-screen-element';
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
    // here is jQuery plugin that binds an event listener that is always triggered before any others
    // This hasn't been fully tested.
    // It relies on the internals of the jQuery framework not changing (only tested with 1.5.2).
    // It will not necessarily get triggered before event listeners that are bound in any way other than as
    // an attribute of the source element or using jQuery bind() and other associated functions.
    $.fn.bindFirst = function( /*String*/ eventType, /*[Object])*/ eventData, /*Function*/ handler) {
        var indexOfDot = eventType.indexOf(".");
        var eventNameSpace = indexOfDot > 0 ? eventType.substring(indexOfDot) : "";

        eventType = indexOfDot > 0 ? eventType.substring(0, indexOfDot) : eventType;
        handler = handler == undefined ? eventData : handler;
        eventData = typeof eventData == "function" ? {} : eventData;

        return this.each(function() {
            var $this = $(this);
            var currentAttrListener = this["on" + eventType];

            if (currentAttrListener) {
                $this.bind(eventType, function(e) {
                    return currentAttrListener(e.originalEvent);
                });

                this["on" + eventType] = null;
            }

            $this.bind(eventType + eventNameSpace, eventData, handler);

            var allEvents = $this.data("events") || $._data($this[0], "events");
            var typeEvents = allEvents[eventType];
            var newEvent = typeEvents.pop();
            typeEvents.unshift(newEvent);
        });
    };
    $.windowBorders = ["left", "right", "top", "bottom"];
    var initCenterElementContent = function() {
        $($.centerElementContent).wrap('<div></div>');
        $($.centerElementContent).each(function(index, element) {
            var wrapper = $(element).parent();
            var padding = {};
            $.windowBorders.forEach(function(item) {
                padding[item] = $(element).css("padding-" + item);
                $(element).css("padding-" + item, "0px");
            });
            ["id", "style", "class"].forEach(function(item) {
                var attribute = $(element).attr(item);
                if (attribute) {
                    $(wrapper).attr(item, attribute);
                }
            });
            ["id", "style", "class"].forEach(function(item) {
                var attribute = $(element).attr(item);
                if (attribute) {
                    $(element).removeAttr(item);
                }
            });
            $.windowBorders.forEach(function(item) {
                $(element).css("padding-" + item, padding[item]);
            });
            $(element).css("display", "inline-block");
            $(element).css("position", "relative");
            $(element).css("text-align", $(wrapper).css("text-align"));
            $(wrapper).css("text-align", "center");
            $(element).css("top", "auto");
            $(element).css("left", "auto");
            $(element).css("right", "auto");
            $(element).css("bottom", "auto");
            $(wrapper).css("minWidth", $(element).width() + "px");
            $(wrapper).css("minHeight", $(element).height() + "px");
            $(element).resize(function(e) {
                var wrapper = $(this).parent();
                var maxWidth = $(wrapper).width() + "px";
                var maxHeight = $(wrapper).height() + "px";
                $(this).css("maxWidth", maxWidth);
                $(this).css("maxHeight", maxHeight);
                $(this).css("margin-top", (($(wrapper).height() - $(this).height()) / 2) + "px");
                $(this).css("margin-left", "auto");
                $(this).css("margin-right", "auto");
                $(this).css("margin-bottom", "auto");
            });
            $(wrapper).resize(function(e) {
                var maxWidth = $(this).width() + "px";
                var maxHeight = $(this).height() + "px";
                $(this).children().each(function(index, element) {
                    $(element).css("maxWidth", maxWidth);
                    $(element).css("maxHeight", maxHeight);
                    $(element).css("margin-top", (($(this).height() - $(element).height()) / 2) + "px");
                    $(element).css("margin-left", "auto");
                    $(element).css("margin-right", "auto");
                    $(element).css("margin-bottom", "auto");
                });
            });
        });
    };
    var updateFullScreenElement = function() {
        $($.fullScreenElement + $.centerElementContent).each(function(index, element) {
            $(element).css("minWidth", $(window).width() + "px");
            $(element).css("minHeight", $(window).height() + "px");
        });
    };
    $.centerElementContent = '.center-element-content';
    var updateCenterElementContent = function() {
        $($.centerElementContent).each(function(index, wrapper) {
            var maxWidth = $(wrapper).width() + "px";
            var maxHeight = $(wrapper).height() + "px";
            $(wrapper).children().each(function(index, element) {
                $(element).css("maxWidth", maxWidth);
                $(element).css("maxHeight", maxHeight);
                $(element).css("margin-top", (($(wrapper).height() - $(element).height()) / 2) + "px");
                $(element).css("margin-left", "auto");
                $(element).css("margin-right", "auto");
                $(element).css("margin-bottom", "auto");
            });
        });
    };
    $(document).ready(initCenterElementContent);
    $(document).ready(updateFullScreenElement);
    $(document).ready(updateCenterElementContent);
    $(document).resize(updateFullScreenElement);
    $(document).resize(updateCenterElementContent);
    $(window).resize(updateFullScreenElement);
    $(window).resize(updateCenterElementContent);
    $(document).ajaxComplete(updateFullScreenElement);
    $(document).ajaxComplete(updateCenterElementContent);
    $(document).ajaxStop(updateFullScreenElement);
    $(document).ajaxStop(updateCenterElementContent);
})(jQuery);