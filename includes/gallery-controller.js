(function($, exports){
  
  var hasFeedback = (window.app && app.Feedback);
  
  //Create a new page type controller called GalleryController.
  var GalleryController = PageType.sub({
    
    //Define the tabs to be created.
    tabs: {
      'Gallery': 'galleryTab'
    },
    
    //Define the elements for jsFramework to keep track of.
    elements: {
      'category_list': '.category_list',
      'category': '.category_list a.category',
      'new_category': '#new-category',
      'delete_category': '.category_list .icon-delete'
    },
    
    events: {

      /**
       * Click on a category */
      'click on category': function(e){

        e.preventDefault();

        //Remove active class from all categories.
        this.category.removeClass('active');

        //Add css class 'active' to clicked category.
        $(e.target).addClass('active');

        //Load category.
        this.editCategory($(e.target).attr('data-id'));

      },
      
      /**
       * Click on 'New category' */
      'click on new_category': function(e){

        e.preventDefault();

        //Remove active class from all categories.
        this.category.removeClass('active');

        //Add css class 'active' to clicked category.
        $(e.target).addClass('active');

        //Load category.
        this.editCategory();

      },
      
      /**
       * Click on 'Delete category' */
      'click on delete_category': function(e){
        e.preventDefault();
        this.deleteCategory($(e.target).attr('data-id'));
      }
      
    },
    
    //Return to posts.
    refreshCategories: function(){
      
      var self = this;


      
    },

    /**
     * Init categories
     *
     * @desc Makes categories sortable.
     */
    initCategories: function(){

      var self = this;

      var getc = $.rest('GET', '?rest=simple_gallery/categories/'+self.gallery_id)
        .done(function(result){
          $.each(result, function(i){
            categories[result[i].id] = result[i];
          });
          category_hierarchy = toHierarchy('lft', 'rgt', result);
        });

      $.when(getc)
        .done(function(){
          renderCategories();
        });

      category_list
        
        /* ---------- Nested sortable ---------- */
        .nestedSortable({
          disableNesting: 'no-nest',
          forcePlaceholderSize: true,
          handle: 'div',
          helper: 'clone',
          listType: 'ul',
          items: 'li',
          maxLevels: 7,
          opacity: .6,
          placeholder: 'placeholder',
          revert: 250,
          tabSize: 25,
          tolerance: 'pointer',
          toleranceElement: '> div'
        })
        
        /* ---------- Sort update ---------- */
        .on('sortupdate', function(e){
          
          $.rest(
            'PUT',
            '?rest=simple_gallery/category_hierarchy/'+gallery_id,
            {categories: $(e.target).nestedSortable('toArray', {startDepthCount: 0, attribute: 'data-id', expression: (/()([0-9]+)/), omitRoot: true})}
          ).done(function(result){
            categories = {};
            $.each(result, function(i){
              categories[result[i].id] = result[i];
            });
            category_hierarchy = toHierarchy('lft', 'rgt', result);
            self.renderCategories();
          });
          
        });

    },

    /**
     * Render categories
     */
    renderCategories: function(){
      
      question_list.find('.question').remove();
      
      var renderer = function(list_target, option_target, data, depth){
        
        for(var i = 0; i < data.length; i++){

          var li = $('#tx-gallery-category-li').tmpl(data[i]);
          list_target.append(li);

          renderer($('<ul>').appendTo(li), option_target, data[i]._children, depth+1);

        }
        
      };
      
      renderer(question_list, start_question, question_hierarchy, 0);
      
    },

    /**
     * Delete category
     */
    deleteCategory: function(id){
      
      var self = this;
      
      if(confirm("Are you sure you want to delete this category?")){
     
       if(hasFeedback) app.Feedback.working('Deleting category.');

       $.rest('DELETE', '?rest=simple_gallery/category/'+id).done(function(data){
          self.refreshCategories();
          app.Feedback.success('Deleting category succeeded.');
        }).error(function(){
          app.Feedback.error('Deleting category failed.');
        });

        category_list.find('li[data-id='+id+']').remove();
        
      }
      
    },
    
    /**
     * Edit category
     */
    editCategory: function(id){

      var self = this;

      $.rest('GET', '?rest=gallery/category/'+id).done(function(data){
        
        self.content.empty();
        
        var form = self.definition.templates.categoryEdit.tmpl({
          data: data,
          page_id: self.page,
          timelines: self.timelines,
          force_timeline: self.filters && self.filters.timeline_id ? self.filters.timeline_id : false,
          force_language: self.force_language,
          languages: app.Page.Languages.data.languages
        }).appendTo(self.editingPage);
        
        var imageId = form.find('[name=thumbnail_image_id]')
          , entryImage = form.find('.entry_image')
          , deleteImage = form.find('.delete-entry-image');
        
      });

      //Load section.
      $.ajax({
        url: $(this).attr('href')
      }).done(function(data){
        $("#config-column-2 > .admin-box").html(data);
      });

    },
    
    //Loads entries for this page.
    loadEntries: function(page){
      
      var self = this;

      alert('loadEntries');
      
      //Since we're refreshing, remove dirty flag.
      self.dirty = false;
      
      //Pages start at 1.
      if(page <= 0)
        page = 1;
      
      //Load the page we're on.
      if(!page)
        page = self.entriesPage;
      
      //Store the page we specified.
      else
        self.entriesPage = page;
      
      self.timelinePreview.html('<p class="loading">Loading...</p>');
      self.entryPagination.empty();
      
      //Load a page of entries. (Note: don't hide the future for admins)
      $.rest('GET', '?rest=timeline/entries/'+page, $.extend({}, self.filters, {is_future_hidden: 0, is_past_hidden: 0}))
      
      //When we got them.
      .done(function(result){
        
        //If we ended up with less pages than the page we requested. Get the last page.
        if(result.pages > 0 && result.pages < page)
          return self.loadEntries(result.pages);
        
        self.timelinePreview.empty();
        
        //Insert pagination.
        var page_numbers = {};
        for(var i = 1; i <= result.pages; i++) page_numbers[i] = i;
        self.entryPagination.html(self.definition.templates.entryPagination.tmpl({
          page: parseInt(result.page, 10),
          pages: parseInt(result.pages, 10),
          page_numbers: page_numbers
        }));
        
        var hasEntries = false;
        
        if(result.entries) $.each(result.entries, function(i){
          
          hasEntries = true;
          
          self.templateEntry(this)
            .appendTo(self.timelinePreview);
          
        });
        
        if(!hasEntries){
          self.timelinePreview.html('<p class="no-entries">There are no entries yet.</p>');
        }
        
        //Refresh elements.
        self.refreshElements();
        
        //Make sure the first tab (which has the previews) applies multilingual clauses.
        app.Page.Tabs.state.controllers[0].setMultilanguageSection(
          self.force_language > 0 ? self.force_language :
          app.Page.Languages.currentLanguageData().id
        );
        
      })
      
      .error(function(){
        self.timelinePreview.html('<p class="error">Could not load preview.</p>');
      });
      
    },
    
    //Templates one entry based on entry data.
    templateEntry: function(data){
      
      //Template the entry template.
      return this.definition.templates.entry.tmpl({
        data: data,
        force_language: self.force_language,
        languages: app.Page.Languages.data.languages
      });
      
    },
    
    //Retrieve input data (from the server probably).
    getData: function(pageId){

      console.log('getData');

      var self = this
        , D = $.Deferred()
        , P = D.promise();
      

      //Retrieve input data from the server based on the page ID.
      $.rest('GET', '?rest=simple_gallery/gallery/'+pageId, {})
      
      //In case of success, this is no longer fresh.
      .done(function(d){
        self.page = d.page_id;
        self.gallery_id = d.id;
        D.resolve(d);
      })
      
      //In case of failure, provide default data.
      .fail(function(){
        D.resolve({
          page_id: pageId
        });
      });
      
      return P;
      
    },
    
    //When rendering of the tab templates has been done, do some final things.
    afterRender: function(){
      
      console.log('afterRender');
      
      var self = this;

      //Load preview entries.
      self.initCategories();
      
    },
    
    //Saves the data currently present in the different tabs controlled by this controller.
    save: function(e, pageId){
      
      alert('save');
      //Save the filters (which chains into titles).
      this.compositionForm.trigger('submit');
      
    },
    
    afterSave: function(data){
    }
    
  });

  //Export the namespaced class.
  GalleryController.exportTo(exports, 'cmsBackend.simple_gallery.GalleryController');
  
})(jQuery, window);
