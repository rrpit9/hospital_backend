<?php

    if (! function_exists('gender'))
    {
        function gender()
        {
            return ['Male','Female','Others'];
        }
    }
        
    if (! function_exists('marital'))
    {
        function marital()
        {
            return ['Married','UnMarried','Divorced'];
        }
    }

    if (! function_exists('device_type'))
    {
        function device_type()
        {
            return ['ios','android','web'];
        }
    }


