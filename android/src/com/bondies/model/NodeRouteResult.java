package com.bondies.model;

public class NodeRouteResult {
	private Node node;
	private double distance;
	private int ordering;

	public NodeRouteResult(Node node, double distance, int ordering) {
		this.node = node;
		this.distance = distance;
		this.ordering = ordering;
	}
	public void setDistance(double distance) {
		this.distance = distance;
	}
	public double getDistance() {
		return distance;
	}
	public void setOrdering(int ordering) {
		this.ordering = ordering;
	}
	public int getOrdering() {
		return ordering;
	}
	public void setNode(Node node) {
		this.node = node;
	}
	public Node getNode() {
		return node;
	}
}
