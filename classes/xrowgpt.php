<?php

class xrowgpt
{

    public static function checkDisplayStatus()
    {
        $xrowgptINI = eZINI::instance("xrowgpt.ini");
        if ( $xrowgptINI->hasVariable( 'GeneralSettings', 'Display' ) )
        {
            $display_in_siteaccess = $xrowgptINI->variable( 'GeneralSettings', 'Display' );
        }
        else
        {
            $display_in_siteaccess = $xrowgptINI->variable( 'GeneralSettings', 'DisplayDefault' );
        }

        //check if the siteaccess is allowed to use ads
        if ( $display_in_siteaccess != "disabled")
        {
            $Module = $GLOBALS['eZRequestedModule'];
            $namedParameters = $Module->NamedParameters;

            if ( isset($namedParameters["NodeID"]) && is_numeric($namedParameters["NodeID"]) )
            {
                //check if its a single page exclude
                $node_id = $namedParameters["NodeID"];
                $single_page_excludes = $xrowgptINI->variable( 'GeneralSettings', 'SinglePageExcludes' );
                if ( in_array( $node_id, $single_page_excludes ) )
                {
                    return false;
                }

                //check if the node is excluded by a tree exclude
                $tree_excludes = $xrowgptINI->variable( 'GeneralSettings', 'TreeExcludes' );
                $tpl = eZTemplate::instance();
                $path = array();
                
                if ( $tpl->hasVariable('module_result') )
                {
                    $moduleResult = $tpl->variable('module_result');
                    foreach ( $moduleResult["path"] as $element )
                    {
                        $path[] = $element["node_id"];
                    }
                    
                }
                else if ( isset( $tpl->Variables[""]["node"] ) )
                {
                    //fallback just in case
                    $path = $tpl->Variables[""]["node"]->pathArray();
                }

                foreach ( $path as $path_element )
                {
                    if ( isset($path_element) && in_array( $path_element, $tree_excludes ) )
                    {
                        return false;
                    }
                }
            }
            //return true if no condition kicked us out before
            return true;
        }
        else
        {
            return false;
        }
    }

    public static function getKeyword( $node = false )
    {
        //checks the path array reversive for a matching keyword inside the ini
        $tpl = eZTemplate::instance();
        $xrowgptINI = eZINI::instance("xrowgpt.ini");
        $path = array();
        $uri = "";
        //activate this to run testmode everywhere
        //return "test";

        if ( $tpl->hasVariable('module_result') )
        {
            $moduleResult = $tpl->variable('module_result');
            $uri = $moduleResult["uri"];

            foreach ( $moduleResult["path"] as $element )
            {
                if ( isset( $element["node_id"] ) )
                {
                    $path[] = $element["node_id"];
                }
            }
        }
        else if ( isset( $tpl->Variables[""]["node"] ) )
        {
            //fallback just in case
            $path = $tpl->Variables[""]["node"]->pathArray();
            $uri = $GLOBALS["request_uri"];
        }
        else if ($node != false && $node instanceof eZContentObjectTreeNode )
        {
            //fallback of the fallback
            $path = explode("/", $node->PathString);
            $uri = $node->urlAlias();
        }

        $keywords = $xrowgptINI->variable( 'KeywordSettings', 'KeywordMatching' );
        $ivw_keywords = $xrowgptINI->variable( 'KeywordSettings', 'IVWMatching' );
        //write "test" zone for test module
        if ( $uri == "/xrowgpt/test" )
        {
            return array( "keyword" => "test", "path" => $path, "ivw_keyword" => "test" );
        }
        else if( strpos($uri, "content/search") )
        {
            return array( "keyword" => $xrowgptINI->variable( 'KeywordSettings', 'KeywordDefault' ), "path" => $path, "ivw_keyword" => "suche", "ivw_sv" => "in" );
        }

        foreach ( array_reverse( $path ) as $path_element )
        {
            if ( isset($path_element) && array_key_exists($path_element, $keywords) )
            {
                //stop the foreach and return the matching keyword
                $normal_keyword = $keywords[$path_element];
                break;
            }
        }

        foreach ( array_reverse( $path ) as $path_element )
        {
            if ( isset($path_element) && array_key_exists($path_element, $ivw_keywords) )
            {
                //stop the foreach and return the matching keyword
                $ivw_keyword = $ivw_keywords[$path_element];
                break;
            }
        }
        
        //$ivw_sv = "in"; // in = frabo tag aktiv
        $ivw_sv = "i2"; //frabo tag activ async
        if( end($path) == $xrowgptINI->variable( 'IVWSettings', 'StartPage' ) )
        {
            $ivw_sv = "ke";
        }
        elseif ( $ivw_keyword === $ivw_keywords[$xrowgptINI->variable( 'IVWSettings', 'StartPage' )] )
        {
            unset($ivw_keyword);
        }

        if (isset($normal_keyword) && isset($ivw_keyword) )
        {
            return array( "keyword" => $normal_keyword, "path" => $path, "ivw_keyword" => $ivw_keyword, "ivw_sv" => $ivw_sv );
        }

        //no keyword found, use the default!
        if ( !isset($normal_keyword) && $xrowgptINI->hasVariable( 'KeywordSettings', 'SiteaccessKeywordDefault' ) )
        {
            $normal_keyword = $xrowgptINI->variable( 'KeywordSettings', 'SiteaccessKeywordDefault' );
        }
        elseif( !isset($normal_keyword) )
        {
            $normal_keyword = $xrowgptINI->variable( 'KeywordSettings', 'KeywordDefault' );
        }

        //no ivw keyword found, use the default!
        if ( $xrowgptINI->hasVariable( 'KeywordSettings', 'SiteaccessIVWKeywordDefault' ) )
        {
            $ivw_keyword = $xrowgptINI->variable( 'KeywordSettings', 'SiteaccessIVWKeywordDefault' );
        }
        elseif( !isset($ivw_keyword) )
        {
            $ivw_keyword = $xrowgptINI->variable( 'IVWSettings', 'KeywordDefault' );
        }
        return array( "keyword" => $normal_keyword, "path" => $path, "ivw_keyword" => $ivw_keyword, "ivw_sv" => $ivw_sv );
    }

    // used inside the body for IVW tracking. Must be loaded always \\
    // function loaded by ajax function
    public static function buildIVWCode( $device_info = array(), $node = false )
    {
        $xrowgptINI = eZINI::instance("xrowgpt.ini");
        if ( $xrowgptINI->variable( 'IVWSettings', 'Enabled' ) == "true" )
        {
            //todo, wo kommt die node her?
            $keyword_info = $this->getKeyword( $node );

            if( $device_info["device"] != "desktop" )
            {
                if( $device_info["breakpoint"] <= 2)
                {
                    $device_letter = "t";
                }
                else
                {
                    $device_letter = "m";
                }
                return '<!-- SZM VERSION="2.0" -->
                        <script type="text/javascript">
                        var iam_data = {
                        "st":"hannovin", // site
                        "cp":"' . $keyword_info["ivw_keyword"] . '_'. $device_letter . '", // code SZMnG-System 2.0
                        "sv":"mo"
                        }
                        iom.c(iam_data, 1);
                        </script>
                        <!--/SZM -->';
            }
            else
            {
                return '<!-- SZM VERSION="2.0" -->
                        <script type="text/javascript">
                        var iam_data = {
                        "st":"hannovin", // site
                        "cp":"' . $keyword_info["ivw_keyword"] . '", // code SZMnG-System 2.0
                        "sv":"' . $keyword_info["ivw_sv"] . '", // i2= FRABO TAG aktiv Async, in= FRABO TAG aktiv   ke= deaktiviert (nur auf der Startseite)
                        "co":"kommentar" // comment
                        }
                        iom.c(iam_data, 1);
                        </script>
                        <!--/SZM -->';
            }
        }
        //fallback return empty
        return "";
    }

    public static function buildGPTCode( $device_info = array(), $keyword = array(), $site = ""  )
    {
        $xrowgptINI = eZINI::instance("xrowgpt.ini");
        $oms_code = $xrowgptINI->variable( 'OmsSettings', 'OmsCode' );
        if( $device_info["device"] != "desktop" )
        {
            return '<!-- nugg.ad mobile call -->
            <script type="text/javascript">
                var oms_site="' . $site . '";
                var WLRCMD="";
                var oms_network="' . $keyword["keyword"] . '";
                var nuggn='.$xrowgptINI->variable( 'OmsSettings', 'Nuggn' ).';
                var nugghost="http://"+oms_network+".nuggad.net";
            </script>
            
            <script type="text/javascript" src="http://oms.nuggad.net/javascripts/nuggad-ls.js"></script>
            
            <!-- google mobile gpt -->
            <script type=\'text/javascript\'>
                nuggad.init({"rptn-url": nugghost}, function(api) {
                    api.rc({"nuggn": nuggn});
                });

                (function() {
                    var useSSL = \'https:\' == document.location.protocol;
                    var src = (useSSL ? \'https:\' : \'http:\') +
                    \'//www.googletagservices.com/tag/js/gpt_mobile.js\';
                    document.write(\'<scr\' + \'ipt src="\' + src + \'"></scr\' + \'ipt>\');
                })();
            </script>

            <script type="text/javascript">

                //!-- Aufbereitung WLRCMD Variable --
                var NUGGarr=Array();
                if (typeof WLRCMD !=\'undefined\' && WLRCMD !=\'\')
                { arrALL=WLRCMD.split(";");
                for (TUPL in arrALL) {
                    if (arrALL[TUPL].indexOf(\'=\') !=-1){
                        NUGGarr[arrALL[TUPL].split(\'=\')[0]]=arrALL[TUPL].split(\'=\')[1];
                    }
                }
                }
                //!-- ENDE Aufbereitung WLRCMD Variable --

                if (window.innerWidth >= 800) {
                    googletag.cmd.push(function() {
                        googletag.defineSlot(\'/".$oms_code."/\'+oms_site+\'/\'+oms_zone,[[728, 90],[2, 1], [3, 1], [4, 1], [6, 1]], \'div-gpt-ad-1363251388018-0\').addService(googletag.pubads());
                        googletag.defineSlot(\'/".$oms_code."/\'+oms_site+\'/\'+oms_zone,[[728, 90],[2, 1], [3, 1], [4, 1], [6, 1]], \'div-gpt-ad-1363251388018-1\').addService(googletag.pubads());
                        googletag.defineSlot(\'/".$oms_code."/\'+oms_site+\'/\'+oms_zone,[[728, 90],[2, 1], [3, 1], [4, 1], [6, 1]], \'div-gpt-ad-1363251388018-2\').addService(googletag.pubads());
                        googletag.defineSlot(\'/".$oms_code."/\'+oms_site+\'/\'+oms_zone,[[728, 90],[2, 1], [3, 1], [4, 1], [6, 1]], \'div-gpt-ad-1363251388018-3\').addService(googletag.pubads());
                        googletag.pubads().setTargeting(\'nielsen\',\'1\');
                        if (typeof NUGGarr !=\'undefined\') {
                            for (var key in NUGGarr) {
                                googletag.pubads().setTargeting(key, NUGGarr[key]);
                            }
                        };
                        googletag.enableServices();
                    });

                } else if (window.innerWidth < 400) {
                    googletag.cmd.push(function() {
                        googletag.defineSlot(\'/".$oms_code."/\'+oms_site+\'/\'+oms_zone,[[320, 50],[2, 1], [3, 1], [4, 1], [6, 1]], \'div-gpt-ad-1363251388018-0\').addService(googletag.pubads());
                        googletag.defineSlot(\'/".$oms_code."/\'+oms_site+\'/\'+oms_zone,[[320, 50],[2, 1], [3, 1], [4, 1], [6, 1]], \'div-gpt-ad-1363251388018-1\').addService(googletag.pubads());
                        googletag.defineSlot(\'/".$oms_code."/\'+oms_site+\'/\'+oms_zone,[[320, 50],[2, 1], [3, 1], [4, 1], [6, 1]], \'div-gpt-ad-1363251388018-2\').addService(googletag.pubads());
                        googletag.defineSlot(\'/".$oms_code."/\'+oms_site+\'/\'+oms_zone,[[320, 50],[2, 1], [3, 1], [4, 1], [6, 1]], \'div-gpt-ad-1363251388018-3\').addService(googletag.pubads());
                        googletag.pubads().setTargeting(\'nielsen\',\'1\');
                        if (typeof NUGGarr !=\'undefined\') {
                            for (var key in NUGGarr) {
                                googletag.pubads().setTargeting(key, NUGGarr[key]);
                            }
                        };
                        googletag.enableServices();
                    });
                } else {
                    googletag.cmd.push(function() {
                        googletag.defineSlot(\'/".$oms_code."/\'+oms_site+\'/\'+oms_zone,[[468, 60],[2, 1], [3, 1], [4, 1], [6, 1]], \'div-gpt-ad-1363251388018-0\').addService(googletag.pubads());
                        googletag.defineSlot(\'/".$oms_code."/\'+oms_site+\'/\'+oms_zone,[[468, 60],[2, 1], [3, 1], [4, 1], [6, 1]], \'div-gpt-ad-1363251388018-1\').addService(googletag.pubads());
                        googletag.defineSlot(\'/".$oms_code."/\'+oms_site+\'/\'+oms_zone,[[468, 60],[2, 1], [3, 1], [4, 1], [6, 1]], \'div-gpt-ad-1363251388018-2\').addService(googletag.pubads());
                        googletag.defineSlot(\'/".$oms_code."/\'+oms_site+\'/\'+oms_zone,[[468, 60],[2, 1], [3, 1], [4, 1], [6, 1]], \'div-gpt-ad-1363251388018-3\').addService(googletag.pubads());
                        googletag.pubads().setTargeting(\'nielsen\',\'1\');
                        if (typeof NUGGarr !=\'undefined\') {
                            for (var key in NUGGarr) {
                                googletag.pubads().setTargeting(key, NUGGarr[key]);
                            }
                        };
                        googletag.enableServices();
                    });
                }
            
            </script>
            <!-- Ende Einbau im Header -->';
        }
        else
        {
            $custom_tags = "";
            $path = $keyword["path"];
            
            if ( empty($path) )
            {
                $custom_tags = "googletag.pubads().setTargeting('NodeID', " . $GLOBALS['eZRequestedModuleParams']["module_name"] . "_" . $GLOBALS['eZRequestedModuleParams']["function_name"] . " );";
            }
            else
            {
                $custom_tags = "googletag.pubads().setTargeting('NodeID', " . end($path) . " );";
                foreach( $path as $i => $path_element )
                {
                    $custom_tags += "googletag.pubads().setTargeting('TreeL". $i ."', ". $path_element ." );";
                    if( $i === 5 )
                    {
                        break;
                    }
                }
            }
            
            return "<script type='text/javascript'>
            //Synchron Call
            
            (function() {
                var useSSL = 'https:' == document.location.protocol;
                var src = (useSSL ? 'https:' : 'http:') + '//www.googletagservices.com/tag/js/gpt.js';
                document.write('<scr' + 'ipt src=\"' + src + '\"></scr' + 'ipt>');
            })();
            
            </script>
            
            <script type='text/javascript'>
                googletag.cmd.push(function() {
                    googletag.defineSlot('/".$oms_code."/'+oms_site+'/'+oms_zone, [728, 90], 'oms_gpt_superbanner').addService(googletag.pubads());
                    googletag.defineSlot('/".$oms_code."/'+oms_site+'/'+oms_zone, [[120, 600],[160, 600],[200, 600]], 'oms_gpt_skyscraper').addService(googletag.pubads());
                
                    googletag.defineSlot('/".$oms_code."/'+oms_site+'/'+oms_zone, [468, 60], 'oms_gpt_fullbanner').addService(googletag.pubads());
                    googletag.defineSlot('/".$oms_code."/'+oms_site+'/'+oms_zone, [468, 61], 'oms_gpt_fullbanner1').addService(googletag.pubads());
                    googletag.defineSlot('/".$oms_code."/'+oms_site+'/'+oms_zone, [468, 62], 'oms_gpt_fullbanner2').addService(googletag.pubads());
                    googletag.defineSlot('/".$oms_code."/'+oms_site+'/'+oms_zone, [468, 63], 'oms_gpt_fullbanner3').addService(googletag.pubads());
                
                    googletag.defineSlot('/".$oms_code."/'+oms_site+'/'+oms_zone, [728, 91], 'oms_gpt_superbanner1').addService(googletag.pubads());
                    googletag.defineSlot('/".$oms_code."/'+oms_site+'/'+oms_zone, [728, 91], 'oms_gpt_superbanner2').addService(googletag.pubads());
                    googletag.defineSlot('/".$oms_code."/'+oms_site+'/'+oms_zone, [728, 92], 'oms_gpt_superbanner3').addService(googletag.pubads());
                
                    googletag.pubads().enableSingleRequest();
                    googletag.pubads().enableSyncRendering(); // Add sync rendering mode
                    " .$custom_tags . "
                    
                    <!-- Hier wird das Bundesland definiert -->
                
                    googletag.enableServices();
                    googletag.pubads().setTargeting('bundesland','NI');
                
                    if (typeof WLRCMD !='undefined' && WLRCMD !='')
                    {
                        temp=WLRCMD.split(";");
                        for (var id in temp) {
                            if (temp[id].indexOf('=') != -1){
                                values = temp[id].split('=')[1];
                            
                                for (var id2 in temp) {
                                    if ((temp[id2].indexOf('=') != -1) && (temp[id].split('=')[0] == temp[id2].split('=')[0]) && (id < id2)){
                                    values += ';'+temp[id2].split('=')[1];
                                    delete temp[id2];
                                    }
                                }
                                temp2 = values.split(";");
                                //	console.log(temp[id].split('=')[0]+' '+temp2)
                                //console.log(\"googletag.pubads().setTargeting(\"+temp[id].split('=')[0]+\", \"+temp2+\")\");
                                googletag.pubads().setTargeting(temp[id].split('=')[0], temp2);
                            }
                        }
                    }
                
                });
            </script>";
        }
    }

    public static function getDeviceInformation( $page_width = false )
    {
        if ( $page_width === false )
        {
            eZDebug:writeError("The page width is not unknown");
            return false;
        }

        $information = array();
        $information["size"] = $page_width;
        $xrowgptINI = eZINI::instance("xrowgpt.ini");
        foreach ( $xrowgptINI->group( "BreakPoints" ) as $number => $size)
        {
            if( $page_width > $size )
            {
                $information["breakpoint"] = $number-1;
                break;
            }
        }
        
        //set desktop default
        $information["device"] = "desktop";
        //if breakpoint is above the edge to tablet, override the default to tablet ;)
        if ( $information["breakpoint"] >= $xrowgptINI->variable( 'BreakPointInfos', 'DesktopToTabletEdge' ) )
        {
            $information["device"] = "tablet";
        }

        //if breakpoint is above the edge to mobile, override the default to mobile ;)
        if ( $information["breakpoint"] >= $xrowgptINI->variable( 'BreakPointInfos', 'TabletToMobileEdge' ) )
        {
            $information["device"] = "mobile";
        }

        return $information;
    }

    public static function buildHeaderCode( $device_info = array() )
    {
        $xrowgptINI = eZINI::instance("xrowgpt.ini");
        $string = "";
        
        $show_ads = $this->checkDisplayStatus();
        
        //add ivw script when module is activated
        if ( $xrowgptINI->variable( 'IVWSettings', 'Enabled' ) == "true" )
        {
            $string = '<script type="text/javascript" src="https://script.ioam.de/iam.js"></script>';
        }
        
        //add oms stuff when ads are displayed
        if( $show_ads )
        {
            //TODO: $node statt false. verbessern?
            $keyword_info = $this->getKeyword( false );
            
            $string += '<script language="JavaScript" type="text/javascript">
                        var oms_site = "' . $xrowgptINI->variable( 'OmsSettings', 'OmsSite' ) . '";
                        var oms_zone = "' . $keyword_info["keyword"] . '";
                        </script>
                        <script type="text/javascript" src="/extension/xrowgpt/design/xrowgpt/javascript/omsvjs14_1.js"></script>
                        <script>
                        try
                        {
                            var ystr="";
                                var y_adj="";
                        
                            for (var id in yl.YpResult.getAll()) {
                                c = yl.YpResult.get(id);
                                ystr+= \';y_ad=\'+c.id;
                                if(c.format){
                                    y_adj=\';y_adj=\'+c.format;
                                }
                            }
                            ystr+=y_adj+\';\';
                            WLRCMD=WLRCMD+ystr+segQS+crtg_content;
                        }
                        catch(err)
                        {}
                        </script>';

            if ( count( $device_info ) >= 1 )
            {
                $string += $this->buildGPTCode($device_info, $keyword_info, $xrowgptINI->variable( 'OmsSettings', 'OmsSite' ));
            }
        }
        
        return $string;
    }

    public static function createAdCodes( $banner_fields = array(), $device_info = array() )
    {
        if ( count($banner_fields) == 0 )
        {
            eZDebug:writeError("No bannerfields found.");
            return false;
        }
        
        $xrowgptINI = eZINI::instance("xrowgpt.ini");
        if( !$xrowgptINI->hasSection( "oms_" . $device_info["device"] . "_ads" ) )
        {
            eZDebug:writeError("Could not find AD settings for this device");
            return false;
        }
        else
        {
            $BannerZones = $xrowgptINI->variable( "oms_" . $device_info["device"] . "_ads", "BannerZone" );
        }
        
        $codes = array();
        foreach ( $banner_fields as $zone )
        {
            $id = $BannerZones[$zone];
            //get $id from zone via $ini
            $codes[$zone] = "<div id='".$id."'>
                    <script type='text/javascript'>
                    googletag.cmd.push(function() { googletag.display('".$id."')});
                    </script>
                    </div>";
        }
        return $codes;
    }

    public static function load( $banner_fields = array(), $page_width = false )
    {
        $device_info = $this->getDeviceInformation( $page_width );
        $html = array();
        $html["header"] = $this->buildHeaderCode($device_info);
        $html["ivw"] = $this->buildIVWCode($device_info);
        $html["adcodes"] = $this->createAdCodes($device_info);
        return $html;
    }
}