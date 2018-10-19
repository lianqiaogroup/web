let cache = {};

cache.config = {
    time: {
        expire: 86400000,
    },
};

let localStorageImpl = {
    KEY: 'cache-c357f296-c1ef-4b5f-b3a3-18923bde2f3a',
    serialize(obj){
        return JSON.stringify(obj);
    },
    deserialize(obj){
        return JSON.parse(obj);
    },
    makeKey(key){
        return this.KEY + "." + key;
    },
    remove(key){
        return new Promise((ac, re) => {
            let path = this.makeKey(key);
            localStorage.removeItem(path);
            ac(key);
        });
    },
    each(fn){
        for(let i = 0; i < localStorage.length; ++i){
            let key = localStorage.key(i);
            if(key.startsWith(localStorageImpl.KEY)){
                let val = localStorage.getItem(key);
                fn(key, JSON.parse(val));
            }
        }
    },
    invalidate(pattern){
        let keys = [];
        this.each( (key, val) => {
            if(pattern.test(key.replace(localStorageImpl.KEY, ""))){
                keys.push(key);
            }
        });
        keys.forEach( key => localStorage.removeItem(key) );
        console.log(keys);
    },
    set(key, obj){
        while(true){
            try{
                //if(window.debug==1){window.debug = 0; throw 'exceeded the quota'}
                localStorage.setItem(this.makeKey(key), JSON.stringify({obj, timestamp: +new Date(), expire: cache.config.time.expire}));
                break;
            }catch(e){
                if(e.toString().indexOf('exceeded the quota') > -1){ // LRU
                    let time = 1e100, obj = null, key = null;
                    this.each(function(k, d){
                        console.log(k, d, time);
                        if(d && time > d.timestamp){
                            time = d.timestamp;
                            obj  = d;
                            key  = k;
                        }
                    });
                    if(!key){
                        throw e;
                    }else{
                        localStorage.removeItem(key);
                    }
                }
            }
        }
        return new Promise((ac, re) => {
            ac();
        });
    },
    get(key, fn){
        let path = this.makeKey(key);
        let r =  localStorage.getItem(path);
        let s = r && JSON.parse(r);
        let self = this;
        if(!r || new Date() - s.timestamp >= s.expire){
            localStorage.removeItem(path);
            if(fn){
                return new Promise((ac, re) => {
                    fn().then(result => {
                        self.set(key, result).then(_ => ac(result));
                    }).catch(re);
                });
            }
            return new Promise((ac, re) => re());
        }else{
            return Promise.resolve(s.obj);
        }
    }
};

cache._impl = localStorageImpl;
cache._set = function(key, obj){
    return this._impl.set(key, obj);
};
cache._get = function(key, fn){
    return this._impl.get(key, fn);
};

cache.set = cache._set.bind(cache);
cache.remove = function(key){
    return this._impl.remove(key);
}.bind(cache);

cache.invalidate = function(pattern){
    return this._impl.invalidate(pattern);
}.bind(cache);
cache.get = cache._get.bind(cache);

export default cache;