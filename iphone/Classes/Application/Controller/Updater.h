//
//  Updater.h
//  bondies
//
//  Created by Seppo on 24/02/10.
//  Copyright 2010 Apple Inc. All rights reserved.
//

#import <UIKit/UIKit.h>

@class 	HTTPRequestAsyncronicDownloader, GzipDecompressor;

@interface Updater : UIViewController {
	HTTPRequestAsyncronicDownloader* downloader;
	GzipDecompressor* decompressor;

	IBOutlet UITextField* baseURL;
	IBOutlet UIProgressView* progressBar;
}

- (void) setDownloaderCalls;
- (void) setGzipperCalls;
- (void) setBaseUrl;
- (IBAction) startDownloading;
- (void) downloadUpdated:(HTTPRequestAsyncronicDownloader*)_request;
- (void) downloadFailed:(HTTPRequestAsyncronicDownloader*)_request;
- (void) downloadFinished:(HTTPRequestAsyncronicDownloader*)_request;
- (void) gzipUpdated:(GzipDecompressor*)_decompressor;
- (void) gzipFinished:(GzipDecompressor*)_decompressor;

@end
