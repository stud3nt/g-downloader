import { Injectable } from '@angular/core';

@Injectable({
	providedIn: 'root'
})
export class CacheService {

  	constructor() { }

  	// cache contents
  	protected storage = [];

  	// cache lifetime data
  	protected lifetimes = [];

	/**
	 * Save elements to cache storage;
	 *
	 * @param key
	 * @param value
	 * @param expiration in second (0 = never expires)
	 */
	public set(key: string, value: any, expiration: number = 0) : void {
		let dateTime = new Date().getTime();
		let expirationTime = (expiration > 0) ? dateTime + expiration : 0;

		this.storage[key] = value;
		this.lifetimes[key] = expirationTime;
	}

  	public get(key: string) : any {
		if (this.has(key)) {
			return this.storage[key];
		}

		return null;
	}

	/**
	 * Checks if current storage
	 *
	 * @param key
	 */
	public has(key: string) : boolean {
		if (typeof this.lifetimes[key] !== 'undefined' && typeof this.storage[key] !== 'undefined') {
			if (this.lifetimes[key] > 0) {
				let currentTime = new Date().getTime();

				if (this.lifetimes[key] >= currentTime) {
					return true;
				} else { // clear expired cache keys
					delete this.lifetimes[key];
					delete this.storage[key];
				}
			} else {
				return true;
			}
		}

		return false;
	}

}
