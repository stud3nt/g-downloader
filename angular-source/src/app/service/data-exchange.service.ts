import { Injectable } from '@angular/core';
import { CookieService } from "ngx-cookie-service";
import { HttpClient } from "@angular/common/http";
import { Observable } from "rxjs";
import { KeysType } from "../enum/keys-type";

@Injectable({
	providedIn: 'root'
})
export class DataExchangeService {

    private dataFileUrl: string = null;

  	constructor(
  	    private cookie: CookieService,
        private http: HttpClient
    ) {
  	    this.dataFileUrl = './app/assets/json/'+this.cookie.get(KeysType.InfoExchangeFile)+'.json';
        this.getJSON().subscribe(data => {
            this.storage = data;
        });
    }

  	// cache contents
  	protected storage = [];

    public getJSON(): Observable<any> {
        return this.http.get(
            this.dataFileUrl
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
