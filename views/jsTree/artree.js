function arTreeShowUpdate(url){
//    $.get(url, function(data){
//        $(".ajaxWrapper").html(data);
//        history.pushState({url:url, targetSelector: ".ajaxWrapper"}, "", url);
//    });
    window.location.href = url;
}

function arTreeRename(id, url){
    url = url ? url : jstree.jstree("get_node", id, true).find("a").attr("href");
    url = replaceTreeUrl(url, "treeUpdate");
    jstree.jstree(true).edit(id);
    $(".jstree-rename-input").off("change").on("change",function(){
        var title = $(this).val();
        $.post(url, {attributes:{title:title}}, function(){
            arTreeShowUpdate(replaceTreeUrl(url, "update"));
        });
    });
}

    function arTreeReloadNode(id, data){
    var tree = jstree.jstree(true);
    var node = tree.get_node("node-" + id);
    tree._append_json_data(node, data);
    }

function replaceTreeUrl(url, action){
    return url.replace(/\/\w+\?/,"/"+action+"?");
}
var jstree = $(".tree");
jstree.jstree({
    "core" : {
        "check_callback" : true,
        "animation" : 0
    },
    "plugins" : [
        "contextmenu", "dnd", "search", "types"
    ],
    "dnd"   : {
        "is_draggable"  : true
    },
    "types" : {
        "folder"        : {"icon" : "glyphicon glyphicon-file"}
    },
    search  :   {
        fuzzy   : false
    },
    "contextmenu": {
        show_at_node    : false,
        items:   {
            create  : {
                //icon    : "/favicon.ico",
                //submenu : {},
                label   : "<i class=\"glyphicon glyphicon-plus\" title=\"Create\"></i>",
                action: function(obj){
                    var url = replaceTreeUrl($(obj.reference[0]).attr("href"), "treeCreate");
                    var id = $(obj.reference[0]).data("id");
                    var title = "New node";

                    $.post(url, {"attributes" : {"title" : title} }, function(data){
                        var attributes = JSON.parse(data);
                        var url = replaceTreeUrl(attributes["a_attr"]["href"], "treeUpdate");
                        window.location.href = attributes["a_attr"]["href"];
//                        jstree.jstree(true).create_node(id, attributes);
//                        arTreeRename(attributes["id"], url);
                    });
                }
            },
            rename  : {
                label   : "<i class=\"glyphicon glyphicon-font\" title=\"Rename\"></i>",
                action  : function(obj){
                    var sel = jstree.jstree(true).get_selected();
                    arTreeRename(sel[0], false);
                }
            },
            edit    : {
                label   : "<i class=\"glyphicon glyphicon-pencil\" title=\"Edit\"></i>",
                action  : function(obj){
                    var url = replaceTreeUrl( $(obj.reference[0]).attr("href"), "update");
                    arTreeShowUpdate(url);
                }
            },
            delete: {
                label   : "<i class=\"glyphicon glyphicon-trash\" title=\"Delete\"></i>",
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
        }
    }
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
$(document).on("click", ".jstree li a", function(){
    arTreeShowUpdate($(this).attr("href"));
});