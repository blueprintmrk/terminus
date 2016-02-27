<?php
// src/FileLoader.php

namespace Terminus;

class CommandLoader {

  /**
   * Identifies all loadable file namespaces within a directory
   *
   * @param string $directory Directory to glean namespaces from
   * @return string[]
   */
  public function getNamespaces($directory) {
    $namespaces = [];
    if ($directory && file_exists($directory)) {
      $iterator = new \DirectoryIterator($directory);
      foreach ($iterator as $file) {
        if ($file->isFile()
          && $file->isReadable()
          && $file->getExtension() == 'php'
          && strpos(
            file_get_contents($file->getPathname()),
            'abstract class'
          ) === false
        ) {
          $namespaces[] = $this->convertFilenameToNamespace(
            $file->getPathname()
          );
        } elseif ($file->isDir() && !$file->isDot()) {
          $namespaces = array_merge(
            $namespaces,
            $this->getNamespaces($file->getPathname())
          );
        }
      }
    }
    return $namespaces;
  }

  /**
   * Converts a Terminus file name into a namespace
   *
   * @param string $file_name File name to convert
   * @return string
   */
  private function convertFilenameToNamespace($file_name) {
    $namespace = str_replace(
      [__DIR__, '/', '.php',],
      ['Terminus', '\\', '',],
      $file_name
    );
    return $namespace;
  }

}
