<?php

namespace tomzx\Graphp\VisJs;

use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Exporter\ExporterInterface;
use Fhaculty\Graph\Graph;

class VisJs implements ExporterInterface
{
	/**
	 * @param \Fhaculty\Graph\Graph $graph
	 * @return string
	 */
	public function getOutput(Graph $graph)
	{
		$vertices = [];
		/** @var \Fhaculty\Graph\Vertex $vertex */
		foreach ($graph->getVertices() as $vertex) {
			$data = [
				'id' => $vertex->getId(),
				'label' => $vertex->getAttribute('label') !== null ? $vertex->getAttribute('label') : $vertex->getId(),
			];

			$shape = $vertex->getAttribute('shape');
			if ($shape) {
				$data['shape'] = $shape;
			}

			$vertices[] .= json_encode($data);
			// TODO(tom@tomrochette.com): Add attributes to label
		}
		$output = 'var nodes = ['.PHP_EOL.implode(','.PHP_EOL, $vertices).PHP_EOL.'];'.PHP_EOL;

		$edges = [];
		/** @var \Fhaculty\Graph\Edge\Base $edge */
		foreach ($graph->getEdges() as $edge) {
			$vertices = $edge->getVertices()->getVector();
			$data = [
				'from' => $vertices[0]->getId(),
				'to' => $vertices[1]->getId(),
			];

			if ($edge instanceof Directed) {
				$data['arrows'] = 'to';
			}

			$label = $edge->getAttribute('label');
			if ($label !== null) {
				$data['label'] = $label;
			}

			$edges[] = json_encode($data);
			// TODO(tom@tomrochette.com): Add attributes to label
		}
		$output .= 'var edges = ['.PHP_EOL.implode(','.PHP_EOL, $edges).PHP_EOL.'];';

		return $output;
	}
}
