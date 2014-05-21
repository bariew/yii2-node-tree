function arTreeShowUpdate(url){
    $.get(url, function(data){
        $(".ajaxWrapper").html(data);
        history.pushState({url:url, targetSelector: ".ajaxWrapper"}, "", url);
    });
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
        "animation" : 0,
        "check_callback" : function (operation, node, node_parent, node_position) {
            switch(operation){
                //case "move_node" : return arTreeMove(node, node_parent, node_position);
                default : return true;
            }
        },
       /* "data" : {
            "url" : function (node) {
              return "/node/nodeItem/treeBranch";
            },
            "data" : function (node) {
                return { "id" : node.id != "#"
                    ? node.id.replace("node-", "") : "" };
            }
        }*/
    },
    "plugins" : [
        "contextmenu", "dnd", "search", "types", "state"
    ],
    "dnd"   : {
        "is_draggable"  : function(el){
            return true;
        }
    },
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
            create  : {
                //icon    : "/favicon.ico",
                //submenu : {},
                label   : "<i class=\"fa fa-file-o\" title=\"Create\"></i>",
                action: function(obj){
                    var url = replaceTreeUrl($(obj.reference[0]).attr("href"), "treeCreate");
                    var id = $(obj.reference[0]).data("id");
                    var title = "New node";
                    $.post(url, {"attributes" : {"title" : title} }, function(data){
                        var attributes = JSON.parse(data);
                        var url = replaceTreeUrl(attributes["a_attr"]["href"], "treeUpdate");
                        jstree.jstree(true).create_node(id, attributes);
                        arTreeRename(attributes["id"], url);
                    });
                }
            },
            rename  : {
                label   : "<i class=\"fa fa-font\" title=\"Rename\"></i>",
                action  : function(obj){
                    var sel = jstree.jstree(true).get_selected();
                    arTreeRename(sel[0], false);
                }
            },
            edit    : {
                label   : "<i class=\"fa fa-edit\" title=\"Edit\"></i>",
                action  : function(obj){
                    var url = replaceTreeUrl( $(obj.reference[0]).attr("href"), "update");
                    arTreeShowUpdate(url);
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
            },
            /*copy    : {
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
                    var parent = tree.get_selected();
                    var buffer = tree.get_buffer();
                    url = replaceTreeUrl(buffer.node[0].a_attr["href"], "treeClone");
                    $.post(url, {"pid":parent[0].replace("node-","")}, function(data){
                        window.location.reload();
                    });
                }
            },
            legate  : {
                label   : "<i class=\"fa fa-sitemap\" title=\"Legate\"></i>",
                _disabled : false,
                action  : function(obj){
                    var tree = jstree.jstree(true);
                    var target = tree.get_selected();
                    var buffer = tree.get_buffer();
                    url = replaceTreeUrl(buffer.node[0].a_attr["href"], "treeLegate");
                    $.post(url, {"target":target[0].replace("node-","")}, function(data){
                       // window.location.reload();
                    });
                }
            },*/
            /*type  : {
                label   : "<i class=\"fa fa-picture-o\" title=\"icon\"></i>",
                submenu : {
                    "default"   : {
                        label   : "<i class=\"fa fa-folder\" title=\"Default\"></i>",
                        action: function(obj){
                            changeAttributes(obj, {"type" : "default"});
                        }
                    },
                    "template"   : {
                        label   : "<i class=\"fa fa-folder-o\" title=\"Template\"></i>",
                        action: function(obj){
                            changeAttributes(obj, {"type" : "template"});
                        }
                    },
                    "urgent"   : {
                        label   : "<i class=\"fa fa-warning\" title=\"Urgent\"></i>",
                        action: function(obj){
                            changeAttributes(obj, {"type" : "urgent"});
                        }
                    },
                    "lock"   : {
                        label   : "<i class=\"fa fa-lock\" title=\"Lock\"></i>",
                        action: function(obj){
                            changeAttributes(obj, {"type" : "lock"});
                        }
                    },
                },
            },*/
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
$(document).bind("dnd_move.vakata", function(e, data){
    if($(data.event.target).parents('.ajaxContent').length){
        data.helper.find(".jstree-icon:eq(0)").removeClass("jstree-er").addClass("jstree-ok");
        breakThis();
    };
}).bind("dnd_stop.vakata", function(e, data){
    if(!(id = $(data.event.target).parents('.ajaxContent').data("id"))){
       return;
    };
    var li = data.element;
    $.ajax({
        type: "POST",
        url: replaceTreeUrl(li.href, "treeLegate"),
        data: {target : id},
        success: function(data){ 
            arTreeReloadNode(id, data);
            arTreeShowUpdate(li.href.replace(/\d+/,id));
        },
        error: function(xhr, status, error){ alert(status); }
    });
}).on("click", ".jstree li a", function(){
    arTreeShowUpdate($(this).attr("href"));
});