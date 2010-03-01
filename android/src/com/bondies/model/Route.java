package com.bondies.model;

import java.util.ArrayList;


public class Route {
	final public static int BUS = 1; 
	final public static int TRAIN = 2; 
	final public static int SUBWAY = 4;
	private String name = "";
	private String operator = "";
	private ArrayList<String> fromWays = new ArrayList<String>();
	private ArrayList<String> toWays = new ArrayList<String>();
	private int walkDistance = -1;
	private int type = 0;

	public String getName() {
		return name;
	}

	public void setName(String name) {
		this.name = name;
	}

	public String getOperator() {
		return operator;
	}

	public void setOperator(String operator) {
		this.operator = operator;
	}

	public ArrayList<String> getFromWays() {
		return fromWays;
	}

	public void setFromWays(ArrayList<String> fromWays) {
		this.fromWays = fromWays;
	}

	public ArrayList<String> getToWays() {
		return toWays;
	}

	public void setToWays(ArrayList<String> toWays) {
		this.toWays = toWays;
	}

	public int getWalkDistance() {
		return walkDistance;
	}

	public void setWalkDistance(int walkDistance) {
		this.walkDistance = walkDistance;
	}

	public int getType() {
		return type;
	}

	public void setType(int type) {
		this.type = type;
	}

	static ArrayList<Route> search(int from_id, int from_intersection_id, int to_id, int to_intersection_id, int media) throws NodeNotFoundException {
		//["from_id"]=> string(4) "1280"  ["from_intersection_id"]=> string(4) "1587" ["to_id"]=> string(4) "1280" ["to_intersection_id"]=> string(4) "1583"
		if (media == 0) return null;
		Node from_node = Node.getByStreets(from_id, from_intersection_id);
		Node to_node = Node.getByStreets(to_id, to_intersection_id);
		ArrayList<Route> routes = new ArrayList<Route>();
		if ((media & BUS) > 0) routes.addAll(searchBus(from_node, to_node));
		if ((media & TRAIN) > 0) routes.addAll(searchTrain(from_node, to_node));
		if ((media & SUBWAY) > 0) routes.addAll(searchSubway(from_node, to_node));
		return routes;
	}

	private static ArrayList<Route> searchSubway(Node fromNode,
			Node toNode) {
		ArrayList<Route> routes = new ArrayList<Route>();
		//SQLiteDatabase database = Database.getInstance();
		//Cursor cursor = database.rawQuery("SELECT node.id, node.lat, node.lon FROM node LEFT JOIN way_nodes ON node.id = way_nodes.node_id LEFT JOIN way ON way_nodes.way_id = way.id LEFT JOIN street ON way.street_id = street.id WHERE street.id = ? AND node.id IN (SELECT node.id FROM node LEFT JOIN way_nodes ON node.id = way_nodes.node_id LEFT JOIN way ON way_nodes.way_id = way.id LEFT JOIN street ON way.street_id = street.id WHERE street.id = ?)", new String[] { String.valueOf(streetId), String.valueOf(streetId2) });
        return routes;
	}

	private static ArrayList<Route> searchTrain(Node fromNode,
			Node toNode) {
		ArrayList<Route> routes = new ArrayList<Route>();
		return routes;
	}

	private static ArrayList<Route> searchBus(Node fromNode,
			Node toNode) {
		ArrayList<Route> routes = new ArrayList<Route>();
		return routes;
	}
}
