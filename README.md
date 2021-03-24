# Direwolf Design - WordPress Plugin Template

- Contributors: [Direwolf Design](https://github.com/DirewolfDesign)
- Tags: gutenberg, block, custom, meta, fields, templates
- Requires at least: 5.5
- Tested up to: 5.7
- Requires PHP: 7.2
- Stable Tag: n/a
- License: GPLv3.0 or later
- License URI: http://www.gnu.org/licenses/gpl-3.0.html

**A powerful WordPress Plugin Template for getting set up with new WordPress plugins quickly and easily**

---

*This project is a work in progress. You're more than welcome to clone/fork this repo and develop your own set of defaults for creating your own WordPress plugins!*

---

## The Concept

**The concept for this package is to create a (fairly) standardised plugin template that can be used to instantiate as many WordPress plugins as you need.**

Essentially, you can download the base plugin template, run an installer script from your command line and generate a new plugin in a familiar format, with all of the base requirements and dependencies ready for you to use.

We're making heavy use of PHP 7s namespacing feature to allow for very simple function and class names as well as powering some function overloads without interfering with other themes / plugins that make use of WordPress' native functionality.

> This is still very much an experimental package and is a work in progress. You're welcome to submit a pull request if you have any tweaks / features you think would fit the template, otherwise feel free to clone/fork the repo and create your own template system. The `install` file is fairly self-explanatory at this stage and is heavily commented, so making modifications to suit your own needs should be pretty straight forward.

---

## Getting Started

To get started, clone or download the repo to your local machine and open the plugin directory in your favourite IDE.

In terminal, navigate to your project folder and run the following command:

```
php install
```

You will then be guided through the Plugin Setup Wizard which will ask you to confirm the details of your plugin and will generate the relevant plugin files for you in a separate directory based on the option you select for the `plugin_text_domain` option.

> You can run `php install` as many times as you want to create multiple plugins using this plugin template, each new plugin will be created in it's own directory and can then be installed directly into WordPress.

---

## Installing Your Plugin

Once generated, your plugin can be installed like any other custom WordPress plugin, either by moving or linking it into `wp-content/plugins` on your local machine or by uploading it directly to the `wp-content/plugins` directory on your server.

Once uploaded to your WordPress install, activate your plugin through the `wp-admin/plugins.php` screen in WordPress and you're good to go!

---

## Things To Note...

### Plugin Namespacing

> If you haven't used PHP namespaces before we recommend you read up on them before diving too deeply into the plugin code, although most of what you'll need to know will be added to our Documentation when it's ready.

Each generated plugin is contained within it's own `namespace`, this allows the installer to use the same base code for each plugin without having to carry out too many rewrites while generating the core plugin code.

Namespacing comes with it's own quirks, the main being how you access classes and functions from outside the namespace. To get around this, each plugin comes loaded with a `plugin()` function that allows you to access the base plugin class from outside the namespace.

In order to access a specific plugin's base class, you'll need to invoke the namespaced `plugin()` function at the top of your theme / plugin's `functions.php` file (or whichever file you're working in).

To do this, add the following code to the top of your file:
```
use function {{plugin_namespace}}\plugin as {{plugin_namespace}};
```
> Note: Replace {{plugin_namespace}} with the actual namespace of your plugin. This can be found at the top of the `plugin.php` file in the root directory of your plugin folder.

You can then reference the specific instance of your plugin by calling `{{plugin_namespace}}();` from anywhere within that file;

#### Example:

> For our example, we're going to assume our `{{plugin_namespace}}` is `DirewolfDesign`.

At the top of our `functions.php` file:
```
<?php
/* Standard PHP header stuff here... */

use function DirewolfDesign\plugin as DirewolfDesign;

...
// Usual functions.php type stuff here...
...

function get_plugin_icons() {
    // Get the icons associated with the DirewolfDesign plugin
    $icons = DirewolfDesign()->icons;
    return $icons;
}
```

As you can see, we're simply mapping our namespaced `plugin()` function into an accessible function outside the `DirewolfDesign` namespace by telling PHP to `use function` followed by our namespace (`DirewolfDesign`) and the name of the function we want to call (`plugin`);

### Function Overrides

Some of the default WordPress functions you're used to will have been overridden by the plugin namespace. Before you panic, we haven't changed anything! You can still call all of these functions as you normally would, but any that are called from within your plugin's `namespace` will use the override version defined by your plugin.

The reason we do this is to make it easier for you to do things like registering Custom Post Types, Admin Pages and other plugin related stuff and have the plugin class track what's being registered when so there's less logic for you to have to worry about.

All of the core classes that ship with your plugin will make use of these override functions in various ways, so you can continue developing as usual and the plugin system will handle everything in the background.

> We'll provide an example of this soon!

***Full Readme coming soon...***
