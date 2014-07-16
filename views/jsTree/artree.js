function arTreeShowUpdate(url){
//    $.get(url, function(data){
//        $(".ajaxWrapper").html(data);
//        history.pushState({url:url, targetSelector: ".ajaxWrapper"}, "", url);
//    });
    window.location.href = url;
}

function arTreeRename(id, url, jstree){
    url = url ? url : jstree.jstree("get_node", id, true).find("a").attr("href");
    url = replaceTreeUrl(url, "tree-update");
    jstree.jstree(true).edit(id);
    $(".jstree-rename-input").off("change").on("change",function(){
        var title = $(this).val();
        $.post(url, {attributes:{title:title}}, function(){});
    });
}

function replaceTreeUrl(url, action){
    return url.replace(/\/\w+\?/,"/"+action+"?");
}
