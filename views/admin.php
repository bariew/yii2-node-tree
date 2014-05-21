<?php Yii::app()->clientScript->registerScript('jsTree','

function arTreeRename(id, url){
    url = url ? url : jstree.jstree("get_node", id, true).find("a").attr("href");
    url = replaceTreeUrl(url, "treeUpdate");
    jstree.jstree(true).edit(id);
    $(".jstree-rename-input").off("change").on("change",function(){
        var title = $(this).val();
        $.post(url, {attributes:{title:title}});
    });
}

function replaceTreeUrl(url, action){
    return url.replace(/\/\w+\?/,"/"+action+"?");
}
var nodeCopy;
var jstree = $(".tree");
jstree.jstree({
    "core" : {
        "animation" : 0,
        "check_callback" : true,
        //"expand_selected_onload"    : true,
    },
    "plugins" : [
        "contextmenu", "dnd", "search", "types", "state"
    ],
    "types" : {
        "folder"        : {"icon" : "fa fa-folder"},
        "folder-ext"    : {"icon" : "fa fa-folder-o"},
        "default"       : {"icon" : "fa fa-file"},
        "default-ext"   : {"icon" : "fa fa-file-o"},
    },
    search  :   {
        fuzzy   : false
    },
    "contextmenu": {
        show_at_node    : false,
        items:   {
            create: {
                //icon    : "/favicon.ico",
                //submenu : {},
                label   : "<i class=\"fa fa-file-o\" title=\"Create\"></i>",
                action: function(obj){
                    var url = replaceTreeUrl($(obj.reference[0]).attr("href"), "treeCreate");
                    var id = obj.reference.context.id;
                    var title = "New node";
                    $.post(url, {
                        "attributes" : {
                            "title" : title
                        }
                    }, function(data){
                        var attributes = JSON.parse(data);
                        var new_id = attributes["data-id"];
                        var url = replaceTreeUrl(attributes["href"], "treeUpdate");
                        jstree.jstree(true).create_node(id, {
                            "text"  : title,
                            "id"    : new_id,
                            "li_attr"   : {
                                "id"    : new_id
                            },
                            "a_attr": attributes
                        });
                        arTreeRename(new_id, url);
                    });
                }
            },
            rename: {
                label   : "<i class=\"fa fa-font\" title=\"Rename\"></i>",
                action  : function(obj){
                    var sel = jstree.jstree(true).get_selected();
                    arTreeRename(sel[0], false);
                }
            },
            edit: {
                label   : "<i class=\"fa fa-edit\" title=\"Edit\"></i>",
                action  : function(obj){
                    var url = replaceTreeUrl( $(obj.reference[0]).attr("href"), "update");
                    $.get(url, function(data){
                        $(".ajaxWrapper").html(data);
                    });
                    history.pushState({url:url, targetSelector: ".ajaxWrapper"}, "", url);
                }
            },
            copy    : {
                label   : "<i class=\"fa fa-copy\" title=\"Copy\"></i>",
                action  : function(obj){
                    var tree = jstree.jstree(true);
                    tree.copy(tree.get_selected());
                }
            },
            paste    : {
                label   : "<i class=\"fa fa-paste\" title=\"Paste\"></i>",
                _disabled : false,
                action  : function(obj){
                    var tree = jstree.jstree(true);
                    var parent_id = tree.get_selected();
                    var buffer = tree.get_buffer();
                    url = replaceTreeUrl(buffer.node[0].a_attr["href"], "treeclone");
                    $.post(url, {"pid":parent_id[0].replace("node-","")}, function(data){
                        window.location.reload();
                    });
                }
            },
            legate  : {
                label   : "<i class=\"fa fa-sitemap\" title=\"Legate\"></i>",
                _disabled : false,
                action  : function(obj){
                    var tree = jstree.jstree(true);
                    var parent_id = tree.get_selected();
                    var buffer = tree.get_buffer();
                    url = replaceTreeUrl(buffer.node[0].a_attr["href"], "treelegate");
                    $.post(url, {"pid":parent_id[0].replace("node-","")}, function(data){
                        window.location.reload();
                    });
                }
            },
            delete: {
                label   : "<i class=\"fa fa-trash-o\" title=\"Delete\"></i>",
                action: function(obj) {
                    var url = replaceTreeUrl($(obj.reference[0]).attr("href"), "treeDelete");
                    if(confirm("Delete node?")) {
                        $.get(url, function(){
                            var ref = jstree.jstree(true),
                            sel = ref.get_selected();
                            if(!sel.length) { return false; }
                            ref.delete_node(sel);
                        });
                    }
                }
            }
        },
    },
}).bind("move_node.jstree", function(event, data){
    $.ajax({
        type: "POST",
        url: replaceTreeUrl(data.node.a_attr.href, "treeMove"),
        data: {
            pid     : data.parent.replace("node-", ""),
            position: data.position
        },
        success: function(data){},
        error: function(xhr, status, error){
            alert(status);
        }
    });
});
$(".jstree li a").live("click", function(){
    var url = $(this).attr("href");
    $.get(url, function(data){
        $(".ajaxWrapper").html(data);
        history.pushState({url:url, targetSelector: ".ajaxWrapper"}, "", url);
    });
});
'); ?>
<div>
    <form class="form-search" onsubmit="return false;">
        <input id="jstreeSearch"
               class="input-medium search-query"
               placeholder="search"
               onchange = "$('.tree').jstree(true).search(this.value);"/>
    </form>
</div>
<div class='tree'>
    <ul>
        <?php $viewName = '_' . preg_replace('/.*\/(\w+)\.php$/', '$1', __FILE__); ?>
        <?php foreach($items as $item): ?>
            <?php $this->render($viewName, compact('item', 'viewName')); ?>
        <?php endforeach; ?>
    </ul>
</div>