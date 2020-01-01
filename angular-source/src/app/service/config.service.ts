import { Injectable } from '@angular/core';
import { HttpClient } from "@angular/common/http";
import * as angularConfig from '../../assets/json/angular-config.json';

@Injectable({
  providedIn: 'root'
})

export class ConfigService {

	// base site URL
	public baseUrl = 'http://test.g-downloader.com';

	public urls = {
		base: '',
		api: '',
		download: ''
	};

	// routing array;
	public routing;

	// menu array
	public menu;

	// parsers settings array
	public parsers;

  	constructor(private http: HttpClient) {
		this.routing = angularConfig.default.routing;
		this.menu = <Node[]>angularConfig.default.menu;
		this.parsers = angularConfig.default.parsers;
		this.urls = angularConfig.default.urls;

		if (this.baseUrl.substr(this.baseUrl.length - 1, 1) === '/') {
			this.baseUrl = this.baseUrl.substr(0, (this.baseUrl.length - 1));
		}
	}

}
