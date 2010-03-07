package com.bondies.model;

import java.util.ArrayList;
import java.util.HashMap;

import android.database.Cursor;
import android.database.sqlite.SQLiteDatabase;
import android.util.Log;


public class Route {
	final private static double FROM_WALK_UP_TO = 0.5;
	final private static double TO_WALK_UP_TO = 0.5;
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

	public static ArrayList<Route> search(int from_id, int from_intersection_id, int to_id, int to_intersection_id, int media) throws NodeNotFoundException {
		//["from_id"]=> string(4) "1280"  ["from_intersection_id"]=> string(4) "1587" ["to_id"]=> string(4) "1280" ["to_intersection_id"]=> string(4) "1583"
		if (media == 0) return null;
		Node from_node = Node.getById(256242075); //Node.getByStreets(from_id, from_intersection_id);
		Node to_node = null;//Node.getByStreets(to_id, to_intersection_id);
		ArrayList<Route> routes = new ArrayList<Route>();
		if ((media & BUS) > 0) routes.addAll(searchBus(from_node, to_node));
		if ((media & TRAIN) > 0) routes.addAll(searchTrain(from_node, to_node));
		if ((media & SUBWAY) > 0) routes.addAll(searchSubway(from_node, to_node));
		return routes;
	}

	private static ArrayList<Route> searchSubway(Node fromNode,
			Node toNode) {
		ArrayList<Route> routes = new ArrayList<Route>();
		Rectangle box = fromNode.calculateBox(FROM_WALK_UP_TO);
		SQLiteDatabase database = Database.getInstance();
		Cursor cursor = database.rawQuery("SELECT node.id, node.lat, node.lon, railway_halts.name FROM railway_halts JOIN node ON railway_halts.node_id = node.id AND node.lat > ? AND node.lat < ? AND node.lon > ? AND node.lon <  ?", new String[] { String.valueOf(box.x1), String.valueOf(box.x2), String.valueOf(box.y1), String.valueOf(box.y2) });
		int c = cursor.getCount();
		HashMap<String, NodeRouteResult> fromNodes = new HashMap<String, NodeRouteResult>();
		for (int i = 0; i < c; i++) {
			cursor.moveToPosition(i);
			Node node = new Node(cursor.getInt(0), cursor.getDouble(1), cursor.getDouble(2), cursor.getString(3));
			double distance = fromNode.calculateDistance(node.getLat(), node.getLon());
			if (distance > FROM_WALK_UP_TO) continue;
			Cursor wayCursor = database.rawQuery("SELECT way_id, NULL FROM way_nodes WHERE node_id = ?", new String[] { String.valueOf(node.getNodeId()) });
			int d = wayCursor.getCount();
			for (int j = 0; j < d; j++) {
				wayCursor.moveToPosition(j);
				String wayId = wayCursor.getString(0);
				if (fromNodes.containsKey(wayId) == false || fromNodes.get(wayId).getDistance() > distance) {
					fromNodes.put(wayId, new NodeRouteResult(node, distance, 0));
				}
			}
		}
		Log.d("C", String.valueOf(fromNodes.size()));

		//SELECT way_id, NULL FROM way_nodes WHERE node_id = 256242143
		//SELECT way_id, NULL FROM way_nodes WHERE node_id = 256242144
		//SELECT way_id, NULL FROM way_nodes WHERE node_id = 256242160
		//SELECT DISTINCT way.name FROM node JOIN way_nodes ON node.id = way_nodes.node_id JOIN way ON way_nodes.way_id = way.id WHERE node.id = 
		//SELECT way_id, NULL FROM way_nodes WHERE node_id = 256242248
		//SELECT way_id, NULL FROM way_nodes WHERE node_id = 256242329
		//SELECT way_id, NULL FROM way_nodes WHERE node_id = 256242251
		//SELECT way_id, NULL FROM way_nodes WHERE node_id = 256242253
		//SELECT way_id, NULL FROM way_nodes WHERE node_id = 256244943
		//SELECT way_id, NULL FROM way_nodes WHERE node_id = 256247262
		//SELECT way.name, railway.operator FROM railway LEFT JOIN way ON railway.way_id = way.id WHERE railway.way_id = 26198543
		//SELECT DISTINCT way.name FROM node JOIN way_nodes ON node.id = way_nodes.node_id JOIN way ON way_nodes.way_id = way.id WHERE node.id = 256242143
		//SELECT DISTINCT way.name FROM node JOIN way_nodes ON node.id = way_nodes.node_id JOIN way ON way_nodes.way_id = way.id WHERE node.id = 256242251

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
