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
        return array( "ad_header_code", "ad_body_code" );
    }

    function modify( $tpl, $operatorName, $operatorParameters, &$rootNamespace, &$currentNamespace, &$operatorValue, &$namedParameters )
    {
        if ($operatorName == "ad_header_code")
        {
            $operatorValue = xrowgpt::buildHeaderCode();
        }
        elseif ($operatorName == "ad_body_code")
        {
            $operatorValue = xrowgpt::buildIVWCode();
        }
    }
}

?>