* 2.0.1 (2020-10-19)

 * Update composer

* 1.0.3 (2017-04-20)

 * fixed a bug about macros file calling : rename SBORuntimeBundle:layout:macros.html.twig to SBORuntimeBundle:layout:sbo-macros.html.twig to avoid self-extending
 * be carefull SBORuntimeBundle:layout:macros.html.twig has been deleted. Call SBORuntimeBundle:layout:sbo-macros.html.twig instead of
 * the avanzu base template is now override to change macro calling : SBORuntimeBundle:layout:base-layout.html.twig
 * in AppKernel, call SBORuntimeBundle before AvanzuBundle to get form theme of sbo instead of avanzu