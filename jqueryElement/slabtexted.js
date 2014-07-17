////////////////////////////////////////////
//
// Вызов плагина slabText
//
// Разработчик Дмитрий Протопопов http://protopopov.ru
// RBA DESIGN INTERNATIONAL LLC http://rbadesign.us
//
// Совместимо с centerElementContent
// Совместимо с fullScreenElement
// Совместимо с windowBorderElement
/*
<link href="/CMSPages/GetResource.ashx?stylesheetname=slabtext" rel="stylesheet" type="text/css" />
<script src="/CMSScripts/Custom/jquery.slabtext.js"></script>
<script src="/CMSScripts/Custom/slabtexted.js"></script>
*/
////////////////////////////////////////////
(function($) {
    $.slabText = '.slabtext';
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
    var initSlabText = function() {
        $($.slabText).each(function(index, element) {
            $(element).slabText();
        });
    };
    $(document).ready(initSlabText);
    $.centerElementContent = '.center-element-content';
    var updateCenterElementContent = function() {
        $($.centerElementContent + $.slabText).each(function(index, wrapper) {
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
    $(document).ready(updateCenterElementContent);
    $.fullScreenElement = '.full-screen-element';
    var updateFullScreenElement = function() {
        $($.fullScreenElement).not($.centerElementContent).each(function(index, element) {
            $(element).css("minWidth", $(window).width() + "px");
            $(element).css("minHeight", $(window).height() + "px");
        });
    };
    $(document).ready(updateFullScreenElement);
    $.windowBorders = ["left", "right", "top", "bottom"];
    $.windowBorders.forEach(function(item) {
        $[item + "BorderElement"] = "." + item + "-border-element";
    });
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
    $(document).resize(updateCenterElementContent);
    $(document).resize(updateFullScreenElement);
    $(document).resize(updateWindowBorderElement);
    $(window).resize(updateCenterElementContent);
    $(window).resize(updateFullScreenElement);
    $(window).resize(updateWindowBorderElement);
    $(document).ajaxComplete(updateCenterElementContent);
    $(document).ajaxComplete(updateFullScreenElement);
    $(document).ajaxComplete(updateWindowBorderElement);
    $(document).ajaxStop(updateCenterElementContent);
    $(document).ajaxStop(updateFullScreenElement);
    $(document).ajaxStop(updateWindowBorderElement);
})(jQuery);