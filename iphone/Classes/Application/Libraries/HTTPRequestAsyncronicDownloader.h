//
//  HTTPRequestAsyncronicDownloader.h
//  bondies
//
//  Created by Seppo on 24/02/10.
//  Copyright 2010 Apple Inc. All rights reserved.
//

#import <Foundation/Foundation.h>
#import "HTTPRequestAsyncronic.h"

@interface HTTPRequestAsyncronicDownloader : HTTPRequestAsyncronic {
	SEL updateSelector;
	NSString* target;
	NSFileHandle* file;
	long long expectedContentLength;
}

@property (readonly) NSString* target;
@property (readonly) long long unsigned int downloadedSize;
@property (readonly) long long totalSize;
@property SEL updateSelector;

- (void) callUpdate;

@end
