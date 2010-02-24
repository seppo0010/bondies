//
//  GzipDecompressor.h
//  bondies
//
//  Created by Seppo on 24/02/10.
//  Copyright 2010 Apple Inc. All rights reserved.
//

#import <Foundation/Foundation.h>


@interface GzipDecompressor : NSObject {
	NSString* source;
	NSString* target;
	id callbackObject;
	SEL finishSelector;
	SEL updateSelector;
	
	BOOL success;
}

@property (retain) NSString* source;
@property (retain) NSString* target;
@property (retain) id callbackObject;
@property SEL finishSelector;
@property SEL updateSelector;
@property (readonly) BOOL success;
@property (readonly) long long unsigned int readedSize;
@property (readonly) long long unsigned int totalSize;

- (void) startDecompressing;
- (void) callFinished;
- (void) callUpdate;

@end
