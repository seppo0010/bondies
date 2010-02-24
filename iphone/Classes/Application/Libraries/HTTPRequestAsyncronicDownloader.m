//
//  HTTPRequestAsyncronicDownloader.m
//  bondies
//
//  Created by Seppo on 24/02/10.
//  Copyright 2010 Apple Inc. All rights reserved.
//

#import "HTTPRequestAsyncronicDownloader.h"


@implementation HTTPRequestAsyncronicDownloader

@synthesize updateSelector;

- (long long unsigned int) downloadedSize {
	NSDictionary* dict = [[NSFileManager defaultManager] attributesOfItemAtPath:[self target] error:nil];
	return [dict fileSize];
}
- (long long) totalSize { return expectedContentLength; }

-(void)connection:(NSURLConnection*)connection didReceiveData:(NSData*)data {
	if (file == nil) {
		NSString* _target = self.target;
		[@"" writeToFile:_target atomically:YES encoding:NSUTF8StringEncoding error:nil];
		file = [[NSFileHandle fileHandleForWritingAtPath:_target] retain];
	}
	[file writeData:data];
	[self callUpdate];
}

- (NSString*) target {
	if (target) return target;
	NSArray *paths = NSSearchPathForDirectoriesInDomains(NSDocumentDirectory, NSUserDomainMask, YES);
	NSString *documentsDirectory = [paths objectAtIndex:0];
	return target = [[documentsDirectory stringByAppendingPathComponent:[self filename]] retain];
}

- (void)connection:(NSURLConnection *)_connection didReceiveResponse:(NSURLResponse *)response {
	[super connection:_connection didReceiveResponse:response];
	expectedContentLength = [(NSHTTPURLResponse*)response expectedContentLength];
}

- (void) callUpdate {
	[callbackObject performSelectorOnMainThread:updateSelector withObject:self waitUntilDone:NO];
}

- (void) dealloc {
	[target release];
	[file release];
	[super dealloc];
}
@end
