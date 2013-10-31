(function($, exports){
  
  var _ref, GalleryAdmin = new Class;
  
  GalleryAdmin.include({
    
    //Default values.
    defaults: {
      category: {
        id: -1
      },
      item: {
        id: -1
      },
      ajax: {
        url: ((_ref = window.location.href.split('#')[0]) && (_ref + ( /\?/.test( _ref ) ? '&' : '?' ) + 'section=gallery/json')),
        type: 'GET',
        dataType: 'json',
        contentType: 'application/json',
        processData: true,
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      }
    },
    Options: null,
    
    //Constructor.
    init: function(options){
      
      this.Options = new Class;
      this.Options.extend(this.defaults);
      this.Options.extend(options);

      //Execute with jQuery features, but in object context.
      $(this.proxy(function(){

        this.createContent();

      }));
      
    },

    createContent: function(){
      
      var app = this;
      
      app.Categories = new Controller('#gallery-category-list', 'category-list', {
        
        data: {},

        elements: {
          'new-category': '#new-category',
          'category': '.category'
        },
        
        events: {
          'click on category': 'showCategory'
        },
        
        init: function(){
        },
        
        showCategory: function(e){

          e.preventDefault();

          app.Options.category.id = $(e.target).closest(".category").attr("rel");
          
          // app.Items.loadItems().done(this.proxy(function(data){
            // app.Items.data = data;
            // app.Items.renderItems(data);
          // })).fail(app.ajaxError);
          
          $.ajax({
            url: "?category_id="+app.Options.category.id+"&section=gallery/item_list_v2"
          }).done(function(d){
            app.Items.inner.html(d);
          });
          
        }
        
      });

      app.Items = new Controller('#gallery-item-list', 'item-list', {
        
        elements: {
          'item': '.item',
          'inner': '.inner'
        },
        
        // events: {
          // 'click on item': 'showItem'
        // },
        
        init: function(){
        },
        
        loadItems: function(id){
          return app.ajax({ 
            model: 'items',
            category_id: app.Options.category.id
          });
        },
        
        loadItem: function(id){
          return app.ajax({
            model: 'item',
            item_id: app.Options.item.id
          });
        },

        showItem: function(e){

          e.preventDefault();
          
          app.Options.item.id = $(e.target).closest(".item").attr("rel");

          app.Items.loadItem().done(this.proxy(function(data){
            app.Items.data = data;
            app.Items.renderItem(data);
          })).fail(app.ajaxError);

        },
    
        renderItems: function(data){
          var tmpl = {}, index = 1;
          this.inner.empty();
          for(var i in data){
            console.log(data[i]);
            tmpl = data[i];
            tmpl.thumbnail = data[i].file_name;
            tmpl.index = index;
            this.inner.append($('#gallery-item-view').tmpl(tmpl));
            index++;
          }
          this.refreshElements();
        },

        renderItem: function(data){

          var tmpl = {};

          tmpl = data;
          tmpl.thumbnail = data.file_name;

          this.inner.empty();
          this.inner.append($('#gallery-item-form-view').tmpl(tmpl));
          this.refreshElements();

        }
      
      });

      
    },
    
    ajax: function(){
      
      var options = $.extend({}, this.Options.ajax);
      
      if(arguments.length == 1){
        $.extend(options, {
          data: (arguments[0])
        });
      }
      
      else if(arguments.length == 2){
        $.extend(options, {
          type: arguments[0],
          data: (arguments[0].toUpperCase() == 'GET' ? arguments[1] : JSON.stringify(arguments[1])),
          processData: (arguments[0].toUpperCase() != 'GET')
        });
      }
      
      else if(arguments.length == 3){
        var data = $.extend({model: arguments[1]}, arguments[2]);
        $.extend(options, {
          type: arguments[0],
          data: (arguments[0].toUpperCase() == 'GET' ? data : JSON.stringify(data)),
          processData: (arguments[0].toUpperCase() != 'GET')
        });
      }
      
      return $.ajax(options);
      
    },
    
    ajaxError: function(ajax){
      if(ajax.status<400){
        $("#gallery-wrapper .notice")
          .text("Invalid server response")
          .fadeOut(500, function(){
            var that = $(this);
            setTimeout(function(){
              $(that).html("").hide();
            }, 5000);
          });
      }else{
        $("#gallery-wrapper .notice")
          .text(ajax.statusText)
          .fadeOut(500, function(){
            var that = $(this);
            setTimeout(function(){
              $(that).html("").hide();
            }, 5000);
          });
      }
    }
    
  });
  
  exports.GalleryAdmin = GalleryAdmin;

})(jQuery, window);
