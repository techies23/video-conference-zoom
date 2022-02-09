<?php

namespace Codemanas\VczApi\Data;

/**
 * Logger class for loggin debug messages.
 *
 * Stores file showing API errors for the plugin.
 *
 * @since 3.8.18
 * @author Deepen Bajracharya
 *
 * @package Codemanas\VczApi\Data
 */
class Logger {

	/**
	 * Incremental log, where each entry is an array with the following elements:
	 *
	 *  - timestamp => timestamp in seconds as returned by time()
	 *  - level => severity of the bug; one between debug, warning, error, critical
	 *  - name => name of the log entry, optional
	 *  - message => actual log message
	 */
	protected $log = [];

	/**
	 * Directory where the log will be dumped, without final slash; default
	 * is this file's directory
	 */
	public $log_dir = '';

	/**
	 * File extension for the logs saved in the log dir
	 */
	public $log_file_extension = "log";

	/**
	 * Whether to append to the log file (true) or to overwrite it (false)
	 */
	public $log_file_append = true;

	/**
	 * Absolute path of the log file, built at run time
	 */
	private $log_file_path = '';

	/**
	 * Where should we write/print the output to? Built at run time
	 */
	private $output_streams = [];

	/**
	 * Whether the init() function has already been called
	 */
	private $logger_ready = false;

	/**
	 * Associative array used as a buffer to keep track of timed logs
	 */
	private $time_tracking = [];

	/**
	 * Add a log entry with an informational message for the user.
	 *
	 * @param $message
	 * @param string $name
	 *
	 * @return array
	 */
	public function info( $message, $name = '' ) {
		return $this->add( $message, $name, 'info' );
	}

	/**
	 * Add a log entry with a diagnostic message for the developer.
	 *
	 * @param $message
	 * @param string $name
	 *
	 * @return array
	 */
	public function debug( $message, $name = '' ) {
		return $this->add( $message, $name, 'debug' );
	}

	/**
	 * Add a log entry with a warning message.
	 *
	 * @param $message
	 * @param string $name
	 *
	 * @return array
	 */
	public function warning( $message, $name = '' ) {
		return $this->add( $message, $name, 'warning' );
	}


	/**
	 * Add a log entry with an error - usually followed by
	 * script termination.
	 *
	 * @param $message
	 * @param string $name
	 *
	 * @return array
	 */
	public function error( $message, $name = '' ) {
		return $this->add( $message, $name, 'error' );
	}

	/**
	 * Start counting time, using $name as identifier.
	 *
	 * Returns the start time or false if a time tracker with the same name
	 * exists
	 *
	 * @param string $name
	 *
	 * @return bool|mixed
	 */
	public function time( string $name ) {

		if ( ! isset( $this->time_tracking[ $name ] ) ) {
			$this->time_tracking[ $name ] = microtime( true );

			return $this->time_tracking[ $name ];
		} else {
			return false;
		}
	}


	/**
	 * Stop counting time, and create a log entry reporting the elapsed amount of
	 * time.
	 *
	 * Returns the total time elapsed for the given time-tracker, or false if the
	 * time tracker is not found.
	 *
	 * @param string $name
	 *
	 * @return bool|string
	 */
	public function timeEnd( string $name ) {

		if ( isset( $this->time_tracking[ $name ] ) ) {
			$start        = $this->time_tracking[ $name ];
			$end          = microtime( true );
			$elapsed_time = number_format( ( $end - $start ), 2 );
			unset( $this->time_tracking[ $name ] );
			$this->add( "$elapsed_time seconds", "'$name' took", "timing" );

			return $elapsed_time;
		} else {
			return false;
		}
	}

	/**
	 *  Add an entry to the log.
	 *
	 * This function does not update the pretty log.
	 *
	 * @param $message
	 * @param string $name
	 * @param string $level
	 *
	 * @return array
	 */
	private function add( $message, $name = '', $level = 'debug' ) {

		/* Create the log entry */
		$log_entry = [
			'timestamp' => time(),
			'name'      => $name,
			'message'   => $message,
			'level'     => $level,
		];

		/* Add the log entry to the incremental log */
		$this->log[] = $log_entry;

		/* Initialize the logger if it hasn't been done already */
		if ( ! $this->logger_ready ) {
			$this->init();
		}

		/* Write the log to output, if requested */
		if ( $this->logger_ready && count( $this->output_streams ) > 0 ) {
			$output_line = $this->format_log_entry( $log_entry ) . PHP_EOL;
			foreach ( $this->output_streams as $key => $stream ) {
				fputs( $stream, $output_line );
			}
		}

		return $log_entry;
	}


	/**
	 * Take one log entry and return a one-line human readable string
	 *
	 * @param array $log_entry
	 *
	 * @return string
	 */
	public function format_log_entry( array $log_entry ): string {

		$log_line = "";

		if ( ! empty( $log_entry ) ) {

			/* Make sure the log entry is stringified */
			$log_entry = array_map( function ( $v ) {
				return print_r( $v, true );
			}, $log_entry );

			/* Build a line of the pretty log */
			$log_line .= date( 'c', $log_entry['timestamp'] ) . " ";
			$log_line .= "[" . strtoupper( $log_entry['level'] ) . "] : ";
			if ( ! empty( $log_entry['name'] ) ) {
				$log_line .= $log_entry['name'] . " => ";
			}
			$log_line .= $log_entry['message'];

		}

		return $log_line;
	}

	/**
	 * Create debug directory and set $this->log_dir property.
	 */
	public function create_dir() {
		if ( ! is_dir( ZVC_LOG_DIR ) ) {
			//Create our directory if it does not exist
			mkdir( ZVC_LOG_DIR );
			file_put_contents( ZVC_LOG_DIR . '/' . '.htaccess', 'deny from all' );
			file_put_contents( ZVC_LOG_DIR . '/' . 'index.html', '' );
		}

		//Set Log directory Path
		$this->log_dir = ZVC_LOG_DIR;
	}

	/**
	 * Determine whether an where the log needs to be written; executed only
	 * once.
	 *
	 * An associative array with the output streams. The
	 * keys are 'output' for STDOUT and the filename for file streams.
	 */
	public function init() {

		if ( ! $this->logger_ready ) {

			//Create Directory
			$this->create_dir();

			//Set logs based on date.
			$file_name = date( 'Y-m-d' );

			/* Build log file path */
			if ( file_exists( $this->log_dir ) ) {
				$this->log_file_path = implode( DIRECTORY_SEPARATOR, [ $this->log_dir, $file_name ] );
				if ( ! empty( $this->log_file_extension ) ) {
					$this->log_file_path .= "." . $this->log_file_extension;
				}

				/* Print to log file */
				$mode                                         = $this->log_file_append ? "a" : "w";
				$this->output_streams[ $this->log_file_path ] = fopen( $this->log_file_path, $mode );
			}
		}

		/* Now that we have assigned the output stream, this function does not need
		to be called anymore */
		$this->logger_ready = true;
	}


	/**
	 * Dump the whole log to the given file.
	 *
	 * Useful if you don't know before-hand the name of the log file. Otherwise,
	 * you should use the real-time logging option, that is, the $write_log or
	 * $print_log options.
	 *
	 * The method format_log_entry() is used to format the log.
	 *
	 * @param $file_path {string} $file_path - Absolute path of the output file. If empty,
	 * will use the class property $log_file_path.
	 */
	public function dump_to_file( $file_path = '' ) {

		if ( ! $file_path ) {
			$file_path = $this->log_file_path;
		}

		if ( file_exists( dirname( $file_path ) ) ) {

			$mode        = $this->log_file_append ? "a" : "w";
			$output_file = fopen( $file_path, $mode );

			foreach ( $this->log as $log_entry ) {
				$log_line = $this->format_log_entry( $log_entry );
				fwrite( $output_file, $log_line . PHP_EOL );
			}

			fclose( $output_file );
		}
	}

	/**
	 * Get Log files from the directory
	 *
	 * @return array
	 */
	public static function get_log_files() {
		$files  = @scandir( ZVC_LOG_DIR, SCANDIR_SORT_DESCENDING );
		$result = array();

		if ( ! empty( $files ) ) {
			foreach ( $files as $key => $value ) {
				if ( ! in_array( $value, array( '.', '..' ), true ) ) {
					if ( ! is_dir( $value ) && strstr( $value, '.log' ) ) {
						$result[ sanitize_title( $value ) ] = $value;
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Remove/delete the chosen file.
	 *
	 * @param string $handle Log handle.
	 *
	 * @return bool
	 */
	public static function remove( $handle ) {
		$removed = false;
		$logs    = self::get_log_files();
		$handle  = sanitize_title( $handle );

		if ( isset( $logs[ $handle ] ) && $logs[ $handle ] ) {
			$file = realpath( trailingslashit( ZVC_LOG_DIR ) . $logs[ $handle ] );
			if ( 0 === stripos( $file, realpath( trailingslashit( ZVC_LOG_DIR ) ) ) && is_file( $file ) ) {
				$removed = unlink( $file );
			}
		}

		return $removed;
	}
}