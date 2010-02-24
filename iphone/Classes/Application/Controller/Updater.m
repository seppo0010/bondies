    //
//  Updater.m
//  bondies
//
//  Created by Seppo on 24/02/10.
//  Copyright 2010 Apple Inc. All rights reserved.
//

#import "Updater.h"
#import "HTTPRequestAsyncronicDownloader.h"
#import "GzipDecompressor.h"

@implementation Updater

- (void)viewDidLoad {
    [super viewDidLoad];
	NSString* url = [[NSUserDefaults standardUserDefaults] objectForKey:@"defaultURL"];
	if (url == nil) url = @"http://hq.delapalo.net/~sebastianw/bondies/";
	baseURL.text = url;
	progressBar.hidden = YES;
	[self retain];
}

- (void) setDownloaderCalls {
	downloader.callbackObject = self;
	downloader.successSelector = @selector(downloadFinished:);
	downloader.failureSelector = @selector(downloadFailed:);
	downloader.updateSelector = @selector(downloadUpdated:);
}

- (void) setGzipperCalls {
	decompressor.callbackObject = self;
	decompressor.finishSelector = @selector(gzipFinished:);
	decompressor.updateSelector = @selector(gzipUpdated:);
}

- (void) setBaseUrl {
	NSUserDefaults* userDefaults = [NSUserDefaults standardUserDefaults];
	@synchronized (userDefaults) {
		[userDefaults setObject:baseURL.text forKey:@"defaultURL"];
	}
}

- (IBAction) startDownloading {
	if (downloader != nil) return;
	[self setBaseUrl];
	downloader = [[HTTPRequestAsyncronicDownloader alloc] init];
	[self setDownloaderCalls];
	[downloader requestUrl:[NSURL URLWithString:[NSString stringWithFormat:@"%@/%@", baseURL.text, @"sqlite.db.gz"]]]; 
}

- (void) downloadUpdated:(HTTPRequestAsyncronicDownloader*)_request {
	if (_request.totalSize <= 0) {
		progressBar.hidden = YES;
		return;
	}
	progressBar.hidden = NO;
	progressBar.progress = (float)_request.downloadedSize / _request.totalSize;
}

- (void) downloadFailed:(HTTPRequestAsyncronicDownloader*)_request {
	[downloader release];
	downloader = nil;
}

- (void) downloadFinished:(HTTPRequestAsyncronicDownloader*)_request {
	progressBar.hidden = YES;
	decompressor = [[GzipDecompressor alloc] init];
	decompressor.source = _request.target;
	decompressor.target = @"";
	[self setGzipperCalls];
	[decompressor startDecompressing];
	[downloader release];
	downloader = nil;
}

- (void) gzipUpdated:(GzipDecompressor*)_decompressor {
	int size = _decompressor.totalSize;
	if (size <= 0) {
		progressBar.hidden = YES;
		return;
	}
	progressBar.hidden = NO;
	progressBar.progress = (float)_decompressor.readedSize / _decompressor.totalSize;
}

- (void) gzipFinished:(GzipDecompressor*)_decompressor {
	if (decompressor.success) {
		[[[[UIAlertView alloc] initWithTitle:@"Database success" message:nil delegate:nil cancelButtonTitle:@"OK" otherButtonTitles:nil] autorelease] show];
	} else {
		[[[[UIAlertView alloc] initWithTitle:@"Database failed" message:nil delegate:nil cancelButtonTitle:@"OK" otherButtonTitles:nil] autorelease] show];
	}

	[decompressor release];
	decompressor = nil;
	progressBar.hidden = YES;
}

@end
