<?php

namespace Terminus\Helpers;

use Symfony\Component\Console\Helper\Helper;

/**
 * Class TemplateHelper
 * Render PHP or other types of files using Twig templates
 *
 * @package Terminus\Helpers
 */
class TemplateHelper extends Helper {

  /**
   * @var string
   */
  private $template_root = __DIR__ . '/../../templates';

  /**
   * @inheritdoc
   */
  public function getName() {
    return 'template';
  }

  /**
   * Renders the data using the given options.
   *
   * @param array $options Elements as follow:
   *  string template_name File name of the template to be used
   *  array  data          Context to pass through for template use
   *  array  options       Options to pass through for template use
   * @return string The rendered template
   */
  public function render(array $options = []) {
    $loader   = new \Twig_Loader_Filesystem($this->template_root);
    $twig     = new \Twig_Environment($loader);
    $rendered = $twig->render($options['template_name'], $options);
    return $rendered;
  }

}
