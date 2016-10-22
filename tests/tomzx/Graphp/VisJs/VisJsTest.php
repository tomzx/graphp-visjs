<?php

namespace tomzx\Graphp\VisJs\Test;

use Fhaculty\Graph\Graph;
use PHPUnit_Framework_TestCase;
use tomzx\Graphp\VisJs\VisJs;

class VisJsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \tomzx\Graphp\VisJs\VisJs
     */
    protected $visJs;

    public function setUp()
    {
        parent::setUp();

        $this->visJs = new VisJs();
    }

    public function testGraphEmpty()
    {
        $graph = new Graph();

        $expected = <<<GRAPH
var nodes = [

];
var edges = [

];
GRAPH;

        $actual = $this->visJs->getOutput($graph);
        $this->assertEquals($expected, $actual);
    }

    public function testGraphIsolatedVertices()
    {
        $graph = new Graph();
        $graph->createVertex('a');
        $graph->createVertex('b');
        $expected = <<<GRAPH
var nodes = [
{"id":"a","label":"a"},
{"id":"b","label":"b"}
];
var edges = [

];
GRAPH;

        $actual = $this->visJs->getOutput($graph);
        $this->assertEquals($expected, $actual);
    }

    public function testEscaping()
    {
        $graph = new Graph();
        $graph->createVertex('a');
        $graph->createVertex('b¹²³ is; ok\\ay, "right"?');
        $graph->createVertex(3);
        $graph->createVertex(4)->setAttribute('graphviz.label', 'normal');
        $expected = <<<GRAPH
var nodes = [
{"id":"a","label":"a"},
{"id":"b\u00b9\u00b2\u00b3 is; ok\\\\ay, \"right\"?","label":"b\u00b9\u00b2\u00b3 is; ok\\\\ay, \"right\"?"},
{"id":3,"label":3},
{"id":4,"label":4,"graphviz.label":"normal"}
];
var edges = [

];
GRAPH;

        $actual = $this->visJs->getOutput($graph);
        $this->assertEquals($expected, $actual);
    }

    public function testGraphDirected()
    {
        $graph = new Graph();
        $graph->createVertex('a')->createEdgeTo($graph->createVertex('b'));
        $expected = <<<GRAPH
var nodes = [
{"id":"a","label":"a"},
{"id":"b","label":"b"}
];
var edges = [
{"from":"a","to":"b","arrows":"to"}
];
GRAPH;

        $actual = $this->visJs->getOutput($graph);
        $this->assertEquals($expected, $actual);
    }

    public function testGraphMixed()
    {
        // a -> b -- c
        $graph = new Graph();
        $graph->createVertex('a')->createEdgeTo($graph->createVertex('b'));
        $graph->createVertex('c')->createEdge($graph->getVertex('b'));
        $expected = <<<GRAPH
var nodes = [
{"id":"a","label":"a"},
{"id":"b","label":"b"},
{"id":"c","label":"c"}
];
var edges = [
{"from":"a","to":"b","arrows":"to"},
{"from":"c","to":"b"}
];
GRAPH;

        $actual = $this->visJs->getOutput($graph);
        $this->assertEquals($expected, $actual);
    }

    public function testGraphUndirectedWithIsolatedVerticesFirst()
    {
        // a -- b -- c   d
        $graph = new Graph();
        $graph->createVertices(array('a', 'b', 'c', 'd'));
        $graph->getVertex('a')->createEdge($graph->getVertex('b'));
        $graph->getVertex('b')->createEdge($graph->getVertex('c'));
        $expected = <<<GRAPH
var nodes = [
{"id":"a","label":"a"},
{"id":"b","label":"b"},
{"id":"c","label":"c"},
{"id":"d","label":"d"}
];
var edges = [
{"from":"a","to":"b"},
{"from":"b","to":"c"}
];
GRAPH;

        $actual = $this->visJs->getOutput($graph);
        $this->assertEquals($expected, $actual);
    }

//    public function testVertexLabels()
//    {
//        $graph = new Graph();
//        $graph->createVertex('a')->setBalance(1);
//        $graph->createVertex('b')->setBalance(0);
//        $graph->createVertex('c')->setBalance(-1);
//        $graph->createVertex('d')->setAttribute('graphviz.label', 'test');
//        $graph->createVertex('e')->setBalance(2)->setAttribute('graphviz.label', 'unnamed');
//        $expected = <<<GRAPH
//var nodes = [
//{"id":"a","label":"a"},
//{"id":"b","label":"b"},
//{"id":"c","label":"c"},
//{"id":"d","label":"d"},
//{"id":"e","label":"e"}
//];
//var edges = [
//
//];
//GRAPH;
//
//        $actual = $this->visJs->getOutput($graph);
//        $this->assertEquals($expected, $actual);
//    }
//
//    public function testEdgeLayoutAtributes()
//    {
//        $graph = new Graph();
//        $graph->createVertex('1a')->createEdge($graph->createVertex('1b'));
//        $graph->createVertex('2a')->createEdge($graph->createVertex('2b'))->setAttribute('graphviz.numeric', 20);
//        $graph->createVertex('3a')->createEdge($graph->createVertex('3b'))->setAttribute('graphviz.textual', "forty");
//        $graph->createVertex('4a')->createEdge($graph->createVertex('4b'))->getAttributeBag()->setAttributes(array(
//            'graphviz.1' => 1,
//            'graphviz.2' => 2
//        ));
//        $graph->createVertex('5a')->createEdge($graph->createVertex('5b'))->getAttributeBag()->setAttributes(array(
//            'graphviz.a' => 'b',
//            'graphviz.c' => 'd'
//        ));
//        $expected = <<<GRAPH
//var nodes = [
//{"id":"1a","label":"1a"},
//{"id":"1b","label":"1b"},
//{"id":"2a","label":"2a"},
//{"id":"2b","label":"2b"},
//{"id":"3a","label":"3a"},
//{"id":"3b","label":"3b"},
//{"id":"4a","label":"4a"},
//{"id":"4b","label":"4b"},
//{"id":"5a","label":"5a"},
//{"id":"5b","label":"5b"}
//];
//var edges = [
//{"from":"1a","to":"1b"},
//{"from":"2a","to":"2b"},
//{"from":"3a","to":"3b"},
//{"from":"4a","to":"4b"},
//{"from":"5a","to":"5b"}
//];
//GRAPH;
//
//        $actual = $this->visJs->getOutput($graph);
//        $this->assertEquals($expected, $actual);
//    }
//
//    public function testEdgeLabels()
//    {
//        $graph = new Graph();
//        $graph->createVertex('1a')->createEdge($graph->createVertex('1b'));
//        $graph->createVertex('2a')->createEdge($graph->createVertex('2b'))->setWeight(20);
//        $graph->createVertex('3a')->createEdge($graph->createVertex('3b'))->setCapacity(30);
//        $graph->createVertex('4a')->createEdge($graph->createVertex('4b'))->setFlow(40);
//        $graph->createVertex('5a')->createEdge($graph->createVertex('5b'))->setFlow(50)->setCapacity(60);
//        $graph->createVertex('6a')->createEdge($graph->createVertex('6b'))->setFlow(60)->setCapacity(70)->setWeight(80);
//        $graph->createVertex('7a')->createEdge($graph->createVertex('7b'))->setFlow(70)->setAttribute('graphviz.label', 'prefixed');
//        $expected = <<<GRAPH
//var nodes = [
//{"id":"1a","label":"1a"},
//{"id":"1b","label":"1b"},
//{"id":"2a","label":"2a"},
//{"id":"2b","label":"2b"},
//{"id":"3a","label":"3a"},
//{"id":"3b","label":"3b"},
//{"id":"4a","label":"4a"},
//{"id":"4b","label":"4b"},
//{"id":"5a","label":"5a"},
//{"id":"5b","label":"5b"},
//{"id":"6a","label":"6a"},
//{"id":"6b","label":"6b"},
//{"id":"7a","label":"7a"},
//{"id":"7b","label":"7b"}
//];
//var edges = [
//{"from":"1a","to":"1b"},
//{"from":"2a","to":"2b"},
//{"from":"3a","to":"3b"},
//{"from":"4a","to":"4b"},
//{"from":"5a","to":"5b"},
//{"from":"6a","to":"6b"},
//{"from":"7a","to":"7b"}
//];
//GRAPH;
//
//        $actual = $this->visJs->getOutput($graph);
//        $this->assertEquals($expected, $actual);
//    }
}
