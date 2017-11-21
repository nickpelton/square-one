<?php

namespace Tribe\Project\Queues\Backends;

use Tribe\Project\Queues\Contracts\Backend;
use Tribe\Project\Queues\Message;

class Mysql implements Backend {

	const DB_TABLE = 's1_queue';

	public function __construct() {
		global $wpdb;

		$this->table_name = $wpdb->prefix . self::DB_TABLE;
	}

	public function enqueue( string $queue_name, Message $message ) {
		global $wpdb;

		$data = $this->prepare_data( $message );
		$data['queue'] = $queue_name;

		return $wpdb->insert( $this->table_name, $data );
	}

	private function prepare_data( $message ) {
		return [
			'task_handler' => $message->get_task_handler(),
			'args'         => serialize( $message->get_args() ),
			'priority'     => $message->get_priority(),
			'taken'        => 0,
		];
	}

	public function dequeue( string $queue_name ) {
		global $wpdb;

		$queue = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $this->table_name
				WHERE queue = %s
				AND taken < %d
				ORDER BY priority ASC
				LIMIT 0,1
				",
				$queue_name,
				time()
			),
			'ARRAY_A'
		);
		$queue['args'] = unserialize( $queue['args'] );

		$wpdb->update(
			$this->table_name,
			[ 'taken' => time() ],
			[ 'id' => $queue['id'] ]
		);

		return new Message( $queue['task_handler'], $queue['args'], $queue['priority'], $queue['id'] );

	}

	public function ack( string $job_id, string $queue_name ) {
		global $wpdb;

		$wpdb->delete(
			$this->table_name,
			[ 'id' => $job_id ]
		);
	}

	public function nack( string $job_id, string $queue_name ) {
		global $wpdb;

		$wpdb->update(
			$this->table_name,
			[ 'taken' => null ],
			[ 'id' => $job_id ]
		);
	}

	public function cleanup() {
		global $wpdb;

		$stale = $wpdb->get_col(
			$wpdb->prepare(
				'SELECT id FROM %s
				WHERE taken > %d
				',
				$this->table_name,
				time() - 5 * DAY_IN_SECONDS
			)
		);

		foreach ( $stale as $id ) {
			$this->nack( $id, 'null' );
		}
	}

	public function get_type(): string {
		return self::class;
	}

	public function count( string $queue_name ): int {
		global $wpdb;

		return $wpdb->get_var(
			"SELECT COUNT(*) FROM $this->table_name"
		);
	}
}
