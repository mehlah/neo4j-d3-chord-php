<?php

use Everyman\Neo4j\Client;
use Everyman\Neo4j\Batch;
use Everyman\Neo4j\Index;
use Everyman\Neo4j\Cypher\Query;

class Graph {

	protected static $nodes = array();
	protected $client = null;

	public function __construct() {
		$this->client = new Client();
	}

	public function create() {
		$names = ['Mehdi', 'Kevin', 'Marie', 'Elo', 'Foof', 'Clement', 'Chris', 'Antoine', 'Julien', 'Nico', 'Yves', 'Pierro'];

		$nodesIndex = new Index($this->client, Index::TypeNode, 'nodes_index');

		$batch = new Batch($this->client);
		foreach ($names as $name) {
			$node = $this->client->makeNode()->setProperty('name', $name);
			static::$nodes[] = $node;
			$batch->save($node); //maybe implicit
			$batch->addToIndex($nodesIndex, $node, 'type', 'User');
		}

		foreach (static::$nodes as $k => $node) {
			// Create random `following` relationships
			$users = range(0, count(static::$nodes) - 1);
			unset($users[$k]);
			$following_ids = array_rand($users, rand(1, 5));
			foreach ((array) $following_ids as $following_id) {
				$follows = $node->relateTo(static::$nodes[$following_id], 'follows');
				$batch->save($follows);
			}
		}

		$batch->commit();
	}

	public function matrix() {
		$queryString = " START a = node:nodes_index(type='User') " .
					   " MATCH a-[:follows]->b " .
					   " RETURN a.name, collect(b.name) ";

		$query = new Query($this->client, $queryString);
		return $query->getResultSet();
	}
}