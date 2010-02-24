//
//  GzipDecompressor.m
//  bondies
//
//  Created by Seppo on 24/02/10.
//  Copyright 2010 Apple Inc. All rights reserved.
//

#import "GzipDecompressor.h"
#import <zlib.h>

#define CHUNK 1024


@implementation GzipDecompressor

@synthesize source, target, callbackObject, finishSelector, updateSelector, success;

- (void) startDecompressing {
	NSAutoreleasePool* pool = [[NSAutoreleasePool alloc] init];
	if ([target length] == 0 || target == nil) target = [[source stringByDeletingPathExtension] retain];

	if ([NSThread isMainThread]) {
		[self performSelectorInBackground:@selector(startDecompressing) withObject:nil];
		return;
	}

	gzFile file = gzopen([source UTF8String], "rb");
	FILE *dest = fopen([target UTF8String], "w");
	unsigned char buffer[CHUNK];
	int err;
	success = TRUE;
	while (TRUE) {
		int uncompressedLength = gzread(file, buffer, CHUNK);
		const char* error = gzerror(file, &err);
		if(fwrite(buffer, 1, uncompressedLength, dest) != uncompressedLength || ferror(dest) || strlen(error) > 0) {
			if (gzeof(file) > 0) break;
			NSLog(@"error writing data");
			success = FALSE;
			break;
		}
		if (gzeof(file) > 0) break;
		[self callUpdate];
	}
	fclose(dest);
	gzclose(file);
	[self callFinished];
	[pool release];
}

- (void) callFinished {
	[callbackObject performSelectorOnMainThread:finishSelector withObject:self waitUntilDone:NO];
}

- (void) callUpdate {
	[callbackObject performSelectorOnMainThread:updateSelector withObject:self waitUntilDone:NO];
}

- (long long unsigned int) readedSize { 
	NSDictionary* dict = [[NSFileManager defaultManager] attributesOfItemAtPath:[self target] error:nil];
	return [dict fileSize];
}
- (long long unsigned int) totalSize {
	NSDictionary* dict = [[NSFileManager defaultManager] attributesOfItemAtPath:[self source] error:nil];
	return [dict fileSize];
}

@end