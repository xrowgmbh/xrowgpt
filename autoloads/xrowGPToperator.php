<?php

class xrowGPToperator
{

    function xrowGPToperator()
    {
    }
    
    function operatorList()
    {
        return array( "ad_header_code", "ad_body_code" );
    }

    function namedParameterPerOperator()
    {
        return true;
    }

    function namedParameterList()
    {
        return array( 
                        'ad_header_code' => array( 'node' => array( 'type' => 'string' , 'required' => false, "default" => false )),
                        'ad_body_code' => array(  'node' => array( 'type' => 'string' , 'required' => false, "default" => false ) )
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
    }
}

?>