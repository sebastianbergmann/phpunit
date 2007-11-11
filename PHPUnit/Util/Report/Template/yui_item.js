        YAHOO.phpunit.container.panel{line} = new YAHOO.widget.Panel("panel{line}", { visible:false, draggable:false, close:true } );
        YAHOO.phpunit.container.panel{line}.setHeader("{header}");
        YAHOO.phpunit.container.panel{line}.setBody("<ul>{tests}</ul>");
        YAHOO.phpunit.container.panel{line}.setFooter("");
        YAHOO.phpunit.container.panel{line}.render("container{line}");
        YAHOO.util.Event.addListener("line{line}", "click", YAHOO.phpunit.container.panel{line}.show, YAHOO.phpunit.container.panel{line}, true);
