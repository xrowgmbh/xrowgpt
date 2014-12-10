{def $start_node = fetch("content", "node", hash( "node_id", 82 ) )}

<h1>this is a test page</h1>
{node_view_gui view='full' content_node=$start_node.data_map.flowblock.content.zones}
{* an alternative is it, to rebuild the zone template *}