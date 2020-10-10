<?php


namespace PostSynchronization\Migrations;


class Migrations {

	const EXECUTED_COMMANDS_OPTION = 'ps_executed_migration_commands';

	public static function defineCommand() {
		\WP_CLI::add_command( 'ps migration', [ self::class, 'handler' ] );
	}

	/**
	 * It runs a migration command.
	 *
	 * <command>
	 * : A name of the command
	 *
	 * [--force-exec=<force-exec>]
	 * : Whether should re-run already executed command
	 */
	public static function handler( array $args, array $assocArgs ) {
		list( $command ) = $args;
		$command = 'PostSynchronization\Migrations\\' . $command;

		if ( ! class_exists( $command ) ) {
			\WP_CLI::error( sprintf( 'Command %s does not exist', $command ) );
			die;
		}

		self::run( $command, $assocArgs['force-exec'] ?? 0, [ \WP_CLI::class, 'log' ] );
	}

	public static function run( string $commandName, bool $forceExec, $observer ) {
		$hasBeenExecuted = self::hasBeenRun( $commandName );
		if ( $forceExec || ! $hasBeenExecuted ) {
			call_user_func( [ $commandName, 'run' ], $observer );

			if ( ! $hasBeenExecuted ) {
				self::markAsExecuted( $commandName );
			}
		} else {
			call_user_func( $observer, sprintf( 'Command %s has already been executed', $commandName ) );
		}
	}

	private static function hasBeenRun( string $command ): bool {
		$executed = get_option( self::EXECUTED_COMMANDS_OPTION, [] );

		return array_key_exists( $command, $executed );
	}

	private static function markAsExecuted( string $command ) {
		$executed             = get_option( self::EXECUTED_COMMANDS_OPTION, [] );
		$executed[ $command ] = 1;

		update_option( self::EXECUTED_COMMANDS_OPTION, $executed );
	}
}