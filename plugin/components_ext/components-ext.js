(function(i18n, CodeMirror) {
	
	document.getElementById('sb_components').firstElementChild.className = 'current';
	
	var notifTimer = null,  // clear this timer if a new notif pops up before the timer is up.
			prevNotif  = null,  // remove previous notification if a new one pops up before the timer is up
			props = [           // properties to be registered for every component
				'slug',
				'title',
				'desc',
				'val',
				'created_dt',
				'modified_dt',
				'last_author'
			];
	
	// generic GS notification service
	var notify = function(type, msg) { 
	  clearTimeout(notifTimer);
	  if (prevNotif && prevNotif.parentNode)
	    prevNotif.parentNode.removeChild(prevNotif);
	
	  var ref = document.getElementsByClassName('bodycontent')[0],
	      notif = document.createElement('div'),
	      close = document.createElement('a');
    notif.className = type;
    notif.innerHTML = msg;
    close.href = 'javascript:void(0)';
    close.innerHTML = '&times;';
		close.className = 'close-btn';
		close.style.textDecoration = 'none';
    close.onclick = function() { clearTimeout(notifTimer); this.parentNode.parentNode.removeChild(this.parentNode); }
    notif.appendChild(close);
	  ref.parentNode.insertBefore(notif, ref);
	  prevNotif = notif;
	  
		$(notif).slideDown(100, function() {
			notifTimer = setTimeout(function() {  
			  $(notif).fadeOut(300, function() {
			    notif.parentNode.removeChild(notif);
			    prevNotif = null;
			  });
			}, 10000);
	  });
	};
	
  // component constructor	
	var Comp = function(el, i) {
	  this.el  = el;
	  this.id  = i;
	  this.oldslug = el.getElementsByClassName('compslug')[0].value;
	  this.form    = el.getElementsByTagName('form')[0];
	  this.data    = {};
	  this.cache   = {};
	  var self = this;
	  
	  props.forEach(function(prop, i) {
	    if (self.el.getElementsByClassName('comp' + prop).length) {
	      var getter = function() { return self.el.getElementsByClassName('comp' + prop)[0].value; }, 
				    setter = function(v) { self.el.getElementsByClassName('comp' + prop)[0].value = v; };
	      
				Object.defineProperty(self, prop, {
				  get: getter,
				  set: setter
				});
			}
		});

		Object.defineProperty(this, 'hasChanged', {
			get: function() {
				return props.map(prop => this[prop] === this.cache[prop]).indexOf(false) > -1
			}
		})
		
		if (CodeMirror)
		  this.CM = CodeMirror.fromTextArea(self.el.getElementsByClassName('compval')[0], {
			  lineNumbers: true,
			  mode: 'php'
			});
	};
	Comp.prototype.slugExists = function() {
	  var others = this.el.parentNode.getElementsByClassName('compdiv'), slug;
	  for (var i = 0, l = others.length; i < l; i++) { 
	    slug = others[i].getElementsByClassName('compslug')[0].value;
	    if (others[i] !== this.el && slug == this.slug)
	      return true;
	  }
	};
	Comp.prototype.edit       = function() {
	  var editBtn = this.el.getElementsByClassName('btn-edit')[0].parentNode;
	  if (!editBtn.className.match('active')) {
	    editBtn.className += (editBtn.className.length ? ' ' : '') + 'active';
	    if (this.hasOwnProperty('CM'))
	      this.val = this.CM.getValue();
	    this.toCache();
	  }
	  if (!this.el.className.match('expanded'))
	    this.el.className += (this.el.className.length ? ' ' : '') + 'expanded';
	};
	Comp.prototype.finishEdit = function() {
	  var editBtn = this.el.getElementsByClassName('btn-edit')[0].parentNode;
	  editBtn.className = editBtn.className.replace(/\s*active/, '');
	  this.el.className = this.el.className.replace(/\s*expanded/, '');
	};
	Comp.prototype.cancel     = function() {
    var conf;
    
    if (this.hasOwnProperty('CM')) 
      this.val = this.CM.getValue();
    
    if (Object.keys(this.cache).length) {
      for (var i = 0, l = props.length; i < l; i++)
		    if (this.cache[props[i]] != this[props[i]]) {
		      conf = confirm(GS.i18n.cancelUpdates);
		      break;
		    }
	  }
	    
		if (conf) {
			this.val  = this.cache.val;
			this.CM.setValue(this.cache.val);
			this.slug = this.cache.slug;
			this.title= this.cache.title;
			this.cache= {};
		}
		
		this.finishEdit();
	};
	Comp.prototype.save       = function() {   
    var self = this,
        nonce = document.getElementsByName('nonce')[0].value;
    
		if (self.hasOwnProperty('CM'))
			self.CM.save();
			
    // no changes were made, return
    if (!self.hasChanged) 
      return;
    // essential data is missing, return & warn
	  if (!self.slug.length || !self.title.length) 
      return notify('error', i18n.error + ': ' + i18n.noSlugOrTitle);
      
    if (self.slugExists()) 
      return notify('error', i18n.error + ': ' + i18n.existing_slug);
      
		$.ajax({
      type: 'POST',
      url : 'load.php?id=components_ext&action=edit&nonce=' + nonce,
      data: $(self.form).serialize() + '&' + $('[name="user"]').serialize(),
      success: function(response) {
        if (response.status == 200) {
          notify('updated', i18n.comp_updated);
			    self.el.getElementsByClassName('modified_dt')[0].textContent     = response.message.modified_dt.split(' ')[0];
			    self.el.getElementsByClassName('modified_dt')[1].textContent     = response.message.modified_dt.split(' ')[1];
			    self.el.getElementsByClassName('modified_by')[0].textContent     = response.message.user;
			    self.form.querySelector('.compslugcode').textContent = self.slug;
			    self.form.querySelector('strong').textContent        = self.title;
			    self.form.querySelector('[name="oldslug"]').value    = self.slug;
			    self.toCache();
        } else {
          notify('error', i18n.error + ': ' + response.message);
        }
      }
    });
	};
	Comp.prototype.remove     = function() {
    var self = this,
        conf  = confirm(i18n.delete_component + ': ' + this.slug + '?'),
        nonce = document.getElementsByName('nonce')[0].value;
    
    if (conf == false || !conf)
      return;
      
		$.ajax({
      type: 'DELETE',
      url : 'load.php?id=components_ext&action=delete&nonce=' + nonce + '&slug=' + self.slug,
      success: function(response) {
        if (response.status == 200) {
	        notify('updated', i18n.comp_deleted);
	        self.el.parentNode.removeChild(self.el);
	        delete self;
        } else {
          notify('error', i18n.error + ': ' + response.message);
        }
      }
    });
	};
	Comp.prototype.toCache    = function() {
		for (var i = 0, l = props.length; i < l; i++)
	    this.cache[props[i]] = this[props[i]];
	};
	Comp.prototype.copyCode   = function() {
	  var sel = window.getSelection(),
	      range = document.createRange();
	  range.selectNodeContents(this);
	  sel.removeAllRanges();
	  sel.addRange(range);
	  
	  try {
	    document.execCommand('copy');
	  } catch (err) {
	  
	  }
	};
	Comp.prototype.init       = function() {
	  var self = this,
	      btnEdit   = self.el.querySelector('.btn-edit'),
	      btnCancel = self.el.getElementsByClassName('btn-cancel')[0],
	      btnSave   = self.el.getElementsByClassName('btn-save')[0],
	      codeSnipp = self.el.querySelector('.comp-snippet'),
	      btnDelete = self.el.querySelector('.btn-delete'),
				compVal   = self.el.getElementsByClassName('compval')[0];
				
		if (self.hasOwnProperty('CM'))
			self.CM.onchange = function(instance, change) { console.log(arguments )}
	     
	  
	  btnSave.addEventListener('click', self.save.bind(self), false);
	  if (btnDelete)
	    btnDelete.addEventListener('click', self.remove.bind(self), false);
	  if (btnEdit)   
		  btnEdit.addEventListener('click', function() {
		    if (!this.parentNode.className.match('active')) {
		      self.edit();
			    if (self.hasOwnProperty('CM'))
			      self.val = self.CM.getValue();
		
		      self.toCache();
		    } else {
		      self.cancel();
		    }
		  }, false);
		if (codeSnipp)
			codeSnipp.addEventListener('dblclick', self.copyCode, false);
	  btnCancel.addEventListener('click', self.cancel.bind(self), false);
	};
	
	// main module
	var module = {};
	module.sortByName = function(a, b) {
		var nA = a.toLowerCase(), 
				nB = b.toLowerCase();
		if (nA < nB) return -1;
		if (nA > nB) return 1;
		return 0;
	};
	module.sortByDate = function(a, b) {
		var dA = new Date(a), 
				dB = new Date(b);
		if (dA > dB) return -1;
		if (dA < dB) return 1;
		return 0;
	};
	module.sort = function(sortProp, invert) {
	  var sortProp = sortProp || 'comptitle', 
	      elems    = document.getElementsByClassName('compdiv'),
	      sorted   = elems; 
	      
	  sorted = Array.prototype.filter.call(sorted, function(el) {
	        return el.id !== 'new-component';
	      }).map(function(el, i) {
	        var e =  el.getElementsByClassName(sortProp)[0];
	        if (!e)
	          e = el.querySelector('[name="' + sortProp.slice(4) + '"]');
	        return e.value;
	      }).sort(sortProp.indexOf('dt') > -1 ? this.sortByDate : this.sortByName);
	  
	  if (invert)
	    sorted.reverse();
	    
	  while (sorted.length) {
	    var e = $('.' + sortProp + '[value="' + sorted.pop() + '"]').closest('.compdiv');
	    if (e[0] && e[0].id !== 'new-component')
	      e[0].parentNode.insertBefore(e[0], e[0].parentNode.children[1]);
	  };
	};
	module.init = function() {
    document.getElementById('btn-new').addEventListener('click', function(e) {
	    var newComp = document.getElementById('new-component');
	    if (newComp.style.display !== 'none')
	      return newComp.getElementsByTagName('input')[0].focus();
	    newComp.style.display = 'block';
	    newComp.getElementsByTagName('input')[0].focus();
	    
	    
		  if (module.newComp.hasOwnProperty('CM') && !module.inited) {
		    // make sure the CodeMirror unit expands
		    module.newComp.CM.setValue('\0');
		    module.newComp.CM.setValue('');
		    module.inited = true;
		  }
	  });
	  
	  document.getElementById('component-sort').addEventListener('change', function(e) {
	    var tgt = e.target;
	    module.sort('comp' + tgt.value, tgt.options[tgt.selectedIndex].hasAttribute('data-invert'));
	  });
	  
	  this.newComp.init();
	  
	  var comps = document.getElementsByClassName('compdiv');
	  for (var i = 1, l = comps.length; i < l; i++)
	    new Comp(comps[i], i).init();
	};
	
	// new component wizard
	module.newComp = new Comp(document.getElementById('new-component'), 0);
	module.newComp.finishEdit = function() {
    if (CodeMirror)
      this.CM.setValue('');
    this.el.style.display = 'none';
    this.val  = '';
    this.slug = '';
    this.title= '';
    this.desc = '';
	};
	module.newComp.renderNew = function(params) {
    var tpl = document.getElementById('tpl-compdiv').innerHTML, 
        count = document.getElementsByClassName('compdiv').length - 1,
        temp  = document.createElement('div');
        
    tpl =  tpl.replace(/%n%/g, count + 1)
              .replace(/%title%/g, params.title)
              .replace(/%slug%/g, params.slug)
              .replace(/%val%/g , params.val);
              
    temp.innerHTML = tpl;
    temp = temp.firstElementChild;
    
    this.el.parentNode.insertBefore(temp, this.el.nextSibling);
    
    new Comp(temp, count).init();
	};
	module.newComp.save = function() {
    var self = this,
        nonce = document.getElementsByName('nonce')[0].value;
    
    // no changes were made, return
    if (!self.hasChanged) 
      return;
    // essential data is missing, return & warn
	  if (!self.slug.length || !self.title.length) 
      return notify('error', i18n.error + ': ' + i18n.noSlugOrTitle);
      
    if (self.slugExists()) 
      return notify('error', i18n.error + ': ' + i18n.existing_slug);
    
    if (CodeMirror)
      self.val = self.CM.getValue();
    
		$.ajax({
      type: 'POST',
      url : 'load.php?id=components_ext&action=add&nonce=' + nonce,
      data: $(self.form).serialize() + '&' + $('[name="user"]').serialize(),
      success: function(response) {
        if (response.status == 200) {
          notify('updated', i18n.comp_created);            
	        self.renderNew({
	          val: self.val,
	          slug: self.slug,
	          title: self.title
	        });
	        self.finishEdit();
	        
        } else {
          notify('error', i18n.error + ': ' + response);
        }
      }
    }).fail(function(response) {
      console.log(response);
    });
	};
	
  // live search
  module.livesearch = {
    entries: []
  };
  module.livesearch.filter = function(srch) {
    var srch = srch.toLowerCase().replace(/[^\w]/g, ''),
        view = [];
    for (var i = 0, l = this.entries.length; i < l; i++)
      if (this.entries[i].indexOf(srch) > -1)
        view.push(this.entries[i]);
    return view;
  }
  module.livesearch.buildsrch = function() {
      var val = this.value.toLowerCase().trim(), first, last, count = 0;
      if (val.length > 2) {
        $('#component-search-list div').each(function(i) {
          if (this.getAttribute('data-value').indexOf(val) > -1) {
            this.style.display = 'block';
            count++;
          } else {
            this.style.display = 'none';
          }
        }); 
        if (count) {
	        $('#component-search-list').show();
	        $('#component-search-list div:visible').eq(0).css('border-top', 'none');
        }
      } else 
        $('#component-search-list').hide();
    };
  module.livesearch.init = function() {
    var cnt = document.createElement('div'), $input;
    
    cnt.innerHTML = document.getElementById('tpl-search').innerHTML;
    document.getElementById('sidebar').insertBefore(cnt.firstElementChild, document.getElementById('sidebar').firstChild);
    
    $input = $('#component-search input')
    $input.on('focus', function() {
      var $els = $('.compdiv'), 
          $list = $('#component-search-list'), html = '';
      
      $els.each(function(index, el) {
        if (index > 0)
        html += '<div data-value="' + this.querySelector('.comptitle').value.toLowerCase() + '">' + this.querySelector('.comptitle').value + '</div>';
      });
      
      $list.html(html);    
      module.livesearch.buildsrch.call(this)
    });
    
    $input.on('keyup', module.livesearch.buildsrch.bind($input[0]));
    
    $('#component-search-list').on('click', 'div', function() {
      var val = this.getAttribute('data-value');
      $('#component-search-list').hide();
      $('.compdiv').each(function(i) {
        var title = this.querySelector('.comp-title');
        if (title && title.textContent.toLowerCase().trim() == val) {
          this.scrollIntoView();
          $(this).find('.btn-edit').click();
        }
      });
    });
    
    $('body').on('click focus', function() {
       if (!$(this).closest('#component-search').length)
        $('#component-search-list').hide();
    });
  };
  module.livesearch.init();
	
	module.init();
  
  // allow entering only [0-9][A-z] and _ for component slug
  $(document.body).on('keypress paste', '.compslug', function(e) {
    var key = e.keyCode || e.which,
        chr = String.fromCharCode(key);
    if (!/\w/.test(chr))
      e.preventDefault();
  });

}(GS.i18n, window.CodeMirror));