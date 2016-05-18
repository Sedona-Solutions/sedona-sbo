Sedona Back Office
==================

Installation
------------

### Requirements

 - Symfony 2.7


### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ composer require "sedona/sbo" "~1"
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project, if you don't use them
already, you also need to add a few more bundles:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

             new FOS\JsRoutingBundle\FOSJsRoutingBundle(),

             new JMS\DiExtraBundle\JMSDiExtraBundle($this),
             new JMS\AopBundle\JMSAopBundle(),

             new Sg\DatatablesBundle\SgDatatablesBundle(),
             new Avanzu\AdminThemeBundle\AvanzuAdminThemeBundle(),

             new Sedona\SBORuntimeBundle\SedonaSBORuntimeBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'), true)) {
              // ...
              $bundles[] = new Sedona\SBOGeneratorBundle\SedonaSBOGeneratorBundle();
        }
        // ...
    }

    // ...
}
```

### Step 3: Create a base layout

Create a template app/Resources/views/layout_admin.html.twig

```
    {% extends 'SedonaSBORuntimeBundle:layout:base-layout.html.twig' %}
    
    {% block avanzu_logo %}
        <a href="{{ path('admin_home') }}" class="logo">
            <img src="{{ asset('bundles/sedonasboadmintheme/images/sedona-logo.png') }}"/> Test SBO
        </a>
    {% endblock %}
    {% block title %}Administration{% endblock %}    
    {% block page_title %}Administration{% endblock %}    
    {% block page_subtitle %}Administration area{% endblock %}
```

### Step 4: Add some basic configuration

Add in routing.yml

    admin:
        resource: "@AppBundle/Controller/Admin"
        type:     annotation
        prefix:   /admin

    fos_js_routing:
        resource: "@FOSJsRoutingBundle/Resources/config/routing/routing.xml"


Add in config.yml

    jms_di_extra:
        locations:
            all_bundles: false
            bundles: [SedonaSBOTestBundle]
            directories: ["%kernel.root_dir%/../src"]


And activate translation in it by uncommenting
            
        translator:      { fallback: "%locale%" }
        

Generator usage
---------------

    php app/console sbo:generate:crud --entity SedonaSBOTestBundle:Track --with-write --overwrite
    
To be working, each Entity should have its __toString() method declared.

Association fields OneToMany are created but commented by default:

* in datatable: before uncommenting, the column name should be changed (entity.name by default)
* in form: only entities with few lines should be uncommented, for long datas, should be replaced by a select2



