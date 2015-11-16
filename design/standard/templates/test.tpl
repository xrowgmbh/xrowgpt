{def $start_node = fetch("content", "node", hash( "node_id", ezini("NodeSettings", "RootNode", "content.ini") ) )}

<h1>this is a test page</h1>
{node_view_gui view='full' content_node=$start_node}
{*node_view_gui view='full' content_node=$start_node.data_map.flowblock.content.zones*}
{*attribute_view_gui attribute=$node.object.data_map.flowblock*}
{* an alternative is it, to rebuild the zone template *}
