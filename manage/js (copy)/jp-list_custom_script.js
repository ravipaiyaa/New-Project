$('document').ready(function () {

    /**
    * user defined functions
    */
    jQuery.fn.jplist.settings = {

        /**
        * LENGTH: jquery ui range slider
        */
        lengthSlider: function ($slider, $prev, $next) {

            $slider.slider({
                min: 0
                ,max: 10000
                ,range: true
                ,values: [0, 10000]
                ,slide: function (event, ui) {
                    $prev.text('Length: ' + ui.values[0]);
                    $next.text(ui.values[1]);
                }
            });
        }

        /**
        * LENGTH: jquery ui set values
        */
        ,lengthValues: function ($slider, $prev, $next) {
            $prev.text('Length: ' + $slider.slider('values', 0));
            $next.text($slider.slider('values', 1));
        }

        /**
        * WIDTH: jquery ui range slider
        */
        ,widthSlider: function ($slider, $prev, $next) {

            $slider.slider({
                min: 0
                ,max: 10000
                ,range: true
                ,values: [0, 10000]
                ,slide: function (event, ui) {
                    $prev.text('Width: ' + ui.values[0]);
                    $next.text(ui.values[1]);
                }
            });
        }

        /**
        * WIDTH: jquery ui set values
        */
        ,widthValues: function ($slider, $prev, $next) {
            $prev.text('Width: ' + $slider.slider('values', 0));
            $next.text($slider.slider('values', 1));
        }

        /**
        * WEIGHT: jquery ui range slider
        */
        ,weightSlider: function ($slider, $prev, $next) {

            $slider.slider({
                min: 0
                ,max: 10000
                ,range: true
                ,values: [0, 10000]
                ,slide: function (event, ui) {
                    $prev.text('Weight: ' + ui.values[0]);
                    $next.text(ui.values[1]);
                }
            });
        }

        /**
        * WEIGHT: jquery ui set values
        */
        ,weightValues: function ($slider, $prev, $next) {
            $prev.text('Weight: ' + $slider.slider('values', 0));
            $next.text($slider.slider('values', 1));
        }
    };

    $('#demo').jplist({
        itemsBox: '.list'
        ,itemPath: '.list-item'
        ,panelPath: '.jplist-panel'
    });
});