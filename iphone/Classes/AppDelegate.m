//
//  AppDelegate.m
//  Project
//
//  Created by Usuario on 28/7/09.
//  Copyright __MyCompanyName__ 2009. All rights reserved.
//

#import "AppDelegate.h"
#import "Updater.h"

@implementation AppDelegate

@synthesize window;


- (void)applicationDidFinishLaunching:(UIApplication *)application {    
    // Override point for customization after application launch
    [window makeKeyAndVisible];
	[window addSubview:[[[[Updater alloc] initWithNibName:@"Updater" bundle:nil] autorelease] view]];
}


- (void)dealloc {
    [window release];
    [super dealloc];
}


@end
