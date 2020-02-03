import { Injectable } from '@angular/core';
import { Observable, Observer } from "rxjs";
import { webSocket } from "rxjs/webSocket";
import { map } from "rxjs/operators";

@Injectable({
	providedIn: 'root'
})

export class WebSocketService {

	public messages: Observable<any>;

	public websocket;

	constructor() { }

	public connect(url: string): Observable<any> {
		this.websocket = webSocket(url);

		return this.create(url);
	}

	private create(url: string): Observable<any> {
		this.messages = Observable.create(
			(observer: Observer<MessageEvent>) => {
				this.websocket.onmessage = observer.next.bind(observer);
				this.websocket.onerror = observer.error.bind(observer);
				this.websocket.onclose = observer.complete.bind(observer);
			}
		).pipe(
			map((response: any) => {
				return response.data
			})
		);

		return this.messages;
	}

	public close(): void {
		this.websocket.close();
	}

}
