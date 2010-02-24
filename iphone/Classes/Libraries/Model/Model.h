#import <UIKit/UIKit.h>
#import "ModelObserver.h"

@class ModelCollection, HTTPRequest;
@interface Model : NSObject <NSCoding> {
	NSMutableSet* observers;
	NSMutableSet* requests;
}

- (Model*) initWithDictionary:(NSDictionary*)dictionary;
- (Model*) setDictionary:(NSDictionary*)dictionary;
+ (NSArray*) fetchList;
+ (NSArray*) find:(NSDictionary*)_filters;
+ (NSArray*) find:(NSDictionary*)_filters orderBy:(NSString*)orderBy;
+ (NSArray*) find:(NSDictionary*)_filters orderBy:(NSString*)orderBy limit:(int)_limit;
+ (NSArray*) find:(NSDictionary*)_filters orderBy:(NSString*)orderBy limit:(int)_limit offset:(int) _offset;
+ (Model*) findOne:(NSDictionary*)_filters;
+ (Model*) findOneById:(int)_id;
+ (void) deleteWhere: (NSMutableDictionary*)dictionary;
+ (void) deleteAll;
- (void) insert;
- (void) update;
- (NSString*) getIdName;
- (void) setId:(int)_id;
- (int) getId;
- (NSDictionary*) toDictionary;
- (void) save;
- (void) delete;
- (NSDictionary*) serialization;

- (void) addObserver:(id<ModelObserver>)_observer;
- (void) removeObserver:(id<ModelObserver>)_observer;
- (void) removeAllObservers;
+ (Model*)fetchFromURL:(NSString*)urlStr;
+ (Model*)fetchFromURL:(NSString*)urlStr withParams:(NSDictionary*)_params;
+ (ModelCollection*)fetchCollectionFromURL:(NSString*)urlStr;
+ (ModelCollection*)fetchCollectionFromURL:(NSString*)urlStr andParams:(NSDictionary*)_params;
- (void) addRequest:(HTTPRequest*)_request;
- (void) removeRequest:(HTTPRequest*)_request;
	
@end
