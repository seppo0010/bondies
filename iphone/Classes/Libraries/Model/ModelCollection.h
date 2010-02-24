//
//  ModelCollection.h
//  circle_of_moms
//
//  Created by Sebastian Waisbrot on 9/17/09.
//  Copyright 2009 __MyCompanyName__. All rights reserved.
//

#import <Foundation/Foundation.h>
#import "ModelCollectionObserver.h"

@class HTTPRequest, JSONHTTPRequest;
@interface ModelCollection : NSObject {
	NSArray* models;
	NSMutableSet* observers;
	Class modelSubclass;
	NSMutableSet* requests;
}

@property (retain) NSArray* models;
@property Class modelSubclass;

- (void) addObserver:(id<ModelCollectionObserver>)_observer;
- (void) removeObserver:(id<ModelCollectionObserver>)_observer;
- (void) removeAllObservers;
- (void) addRequest:(HTTPRequest*)_request;
- (void) removeRequest:(HTTPRequest*)_request;
- (void)setCollectionFromRequest:(JSONHTTPRequest*)request;

@end