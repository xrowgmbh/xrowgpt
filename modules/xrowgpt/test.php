<?php

$Module = $Params['Module'];

$tpl = eZTemplate::factory();

$Result = array();
$Result['content'] = $tpl->fetch( 'design:test.tpl' );
$Result['path'] = array( array( 'url' => false,
                                'text' => 'Werbung Test-Modul' ) );
