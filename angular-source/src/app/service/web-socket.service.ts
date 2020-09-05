import { Injectable } from '@angular/core';
import { webSocket } from "rxjs/webSocket";
import { HttpHelper } from "../helper/http-helper";
import { ConfigService } from "./config.service";
import { RouterService } from "./router.service";
import { CookieService } from "ngx-cookie-service";

@Injectable({
	providedIn: 'root'
})

export class WebSocketService {

	constructor(
		private configService: ConfigService,
		private routerService: RouterService,
		private cookie: CookieService
	) {}

	private _websockets = {};

	public connect(connectionName: string = ''): void {
		try {
			this._websockets[connectionName] = webSocket('ws://127.0.0.1:2002');
		} catch(error) {
			console.log(error);
		}
	}

	public createListener(connectionName: string = '', successFunction: (response) => any, errorFunction: (error) => any, completeFunction: () => any) {
		if (!this.isConnected(connectionName))
			this.connect(connectionName);

		this._websockets[connectionName].subscribe((response) => {
			successFunction(response);
		}, (error) => {
			errorFunction(error);
		}, () => {
			completeFunction();
		});
	};

	public isConnected(connectionName: string = ''): boolean {
		return (
			typeof this._websockets[connectionName] !== 'undefined'
				&&
			!this._websockets[connectionName].closed
		);
	}

	public disconnect(connectionName: string = ''): void {
		if (this.isConnected(connectionName)) {
			this._websockets[connectionName].unsubscribe();
			delete this._websockets[connectionName];
		}
	}

	public sendRequest(connectionName: string = '', operationName: string = null, token: string = null, data: any = {}) {
		if (!this.isConnected(connectionName))
			this.connect(connectionName);

		this._websockets[connectionName].next({
			_operation: operationName,
			_token: token,
			_data: (data ? (HttpHelper.convert(data, HttpHelper.Object)) : null)
		});
	}
}
