import { Injectable } from '@angular/core';
import {CookieService} from "ngx-cookie-service";
import {HttpClient} from "@angular/common/http";
import {Observable} from "rxjs";

@Injectable({
	providedIn: 'root'
})
export class CacheService {

    private cookieFileUrl: string = null;

  	constructor(
  	    private cookie: CookieService,
        private http: HttpClient
    ) {
  	    this.cookieFileUrl = './assets/'+this.cookie.get('cookie_file')+'.json';
        this.getJSON().subscribe(data => {
            this.storage = data;
        });
    }

  	// cache contents
  	protected storage = [];

    public getJSON(): Observable<any> {
        return this.http.get(
            this.cookieFileUrl
        );
    }

  	public get(key: string): object|null {
		if (typeof this.storage[key] !== 'undefined') {
			return this.storage[key];
		}

		return null;
	}

	public clear(): void {
        this.storage = [];
    }
}
