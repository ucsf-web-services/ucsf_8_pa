<?php

namespace EclipseGc\CommonConsole;

final class CommonConsoleEvents {

  const ALIAS_FIND = 'commonconsole.alias.find';

  const GET_PLATFORM_TYPE = 'commonconsole.platform.type';

  const GET_PLATFORM_TYPES = 'commonconsole.platform.types';

  const PLATFORM_WRITE = 'commonconsole.platform.write';

  const PLATFORM_DELETE = 'commonconsole.platform.delete';

  const PLATFORM_BOOTSTRAP = 'commonconsole.platform.bootstrap';

  const OUTPUT_FORMATTER_STYLE = 'commonconsole.output.formatter_style';

  const ADD_PLATFORM_TO_COMMAND = 'commonconsole.command.add_platform';

  const CREATE_APPLICATION = 'commonconsole.input.create_definition';

  /**
   * Dispatched right before a command is executed on a platform.
   *
   * @see \EclipseGc\CommonConsole\Event\PlatformArgumentInjectionEvent
   */
  const PLATFORM_ARGS_INJ = 'commonconsole.platform.argument.injection';

  const FILTER_PLATFORM_SITES = 'commonconsole.platform.sites.filter';
  
  const PLATFORM_CONFIG = 'commonconsole.platform.config';

}
