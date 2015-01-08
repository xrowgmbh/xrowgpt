<?php

class xrowGPToperator
{

    function xrowGPToperator()
    {
    }
    
    function operatorList()
    {
        return array( "ad_header_code", "ad_body_code", "ad_code" );
    }

    function namedParameterPerOperator()
    {
        return true;
    }

    function namedParameterList()
    {
        return array( 
                        'ad_header_code' => array( 'node' => array( 'type' => 'string' , 'required' => false, "default" => false )),
                        'ad_body_code' => array( 'node' => array( 'type' => 'string' , 'required' => false, "default" => false ) ),
                        'ad_code' => array( 'codes' => array( 'type' => 'array' , 'required' => true, "default" => array() ) )
        );
    }

    function modify( $tpl, $operatorName, $operatorParameters, &$rootNamespace, &$currentNamespace, &$operatorValue, &$namedParameters )
    {
        if ( isset($namedParameters['node']) )
        {
            $node = $namedParameters['node']; 
        }
        else
        {
            $node = false;
        }

        if ($operatorName == "ad_header_code")
        {
            $operatorValue = xrowgpt::buildHeaderCode( $node );
        }
        elseif ($operatorName == "ad_body_code")
        {
            $operatorValue = xrowgpt::buildIVWCode( $node );
        }
        elseif ($operatorName == "ad_code")
        {
            $html = "";
            if( xrowgpt::checkDisplayStatus() )
            {
                $code_array = $namedParameters['codes'];

                foreach ( $code_array as $key => $code)
                {
                    $html .='
                    <div id="'. $code .'">
                        <script type="text/javascript">
                            googletag.cmd.push(function() { googletag.display("'. $code .'")});
                        </script>
                    </div>';
                }
            }
            $operatorValue = $html;
        }
    }
}

?>