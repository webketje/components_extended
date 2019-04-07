# GS Components Extended

Augmented functionality for cleaner and more powerful GetSimpleCMS components.

Better UI with sort & search, CodeMirror support, doubleclick code snippet copy, individual component save without page reload. Components Extended saves your components as `<slug>.xml` files in `data/components` with AJAX, one component at a time. It replaces the components tab with a custom tab, and will automatically import existing components from `components.xml` on activation. Available in EN, FR, NL. 

Features
=======
* Keeps track of the created date, modified date, and last editor.
* Edit & rename component slugs and titles (available as `$params->title` in the component) independently.
* Sort & search components
* CodeMirror support by default (unless `GSNOHIGHLIGHT` is **explicitly** set to TRUE).
* Available in: English, German, French, Dutch
* Bonus: Doubleclick a code snippet for instant copy-to-clipboard.

PHP 
===
Components Extended adds one PHP function for components, `get_ext_component($slug, $params = array());` where `$params` is an array of named keys. Eg if you had the following:
<pre><code>&lt;?php get&#95;ext&#95;component($slug, array(
    'greet' => 'Hello',
    'name'  => 'world'
  ));</code></pre>

In your extended component you could do:
<pre><code>&lt;?php echo $params->greet . ' ' . $params->name . '!'; ?&gt;</code></pre>

And it would output: *Hello world!*.

&#x23; Be sure to set the `GSTIMEZONE` constant to your timezone if you wish to have meaningful timestamps for created & modified dates.<br>&#x23; to other plugin developers: the standard GS hooks `component-save` and `component-extras` also work with this plugin.

Screenshot
=========
![Components Extended UI](http://i.imgur.com/kVpZqon.png)

Changelog
=========
<pre><code>
2019-04-08 - v0.9.3  
- Removed spellcheck from component edits  
- Fixed another permissions bug 
- Fixed compatibility issues with GS 3.4  
- Added loading plugin on clicking the main GS 3.4 navigation  
- Removed outdated CSS vendor prefixes  
- Updated some metadata (version, website)  
2019-02-20 - v0.9.2
- minor ui fix
- fix title param  
2019-01-29 - v0.9.1
- Fix permissions issue when saving components
- Add German language
2017-02-21 - v0.9
- Fix escape characters '\\\' from duplicating
- Minor NL translation fix
- Extended components are now mapped back to components.xml on plugin deactivation.
- When an extended component is now deleted, and a component with the same slug 
  exists in components.xml, this one will be deleted too.  
2016-09-24 - v0.8  
- Added 2 sort options (by modified & created date)
- Made .htaccess compatible with Apache 2.4 mod_authz_core
- CodeMirror is now enabled by default
- Fix AJAX issues
- Fix minor JS issues (dynamic update UI, notification error, console log).
- Fix NL translation  
2016-06-18 - v0.7.1
- Bugfix  
2016-06-18 - v0.7
- CSRF (cookie, header & nonce check) vulnerability patch
- Allow changing the directory where components are saved
- Make component title available in component as $params->title
- Bugfix slug<->title when creating new component, labels
- Added languages FR/NL
- CodeMirror support  
2016-06-14 - v0.1
- Initial release</code></pre>