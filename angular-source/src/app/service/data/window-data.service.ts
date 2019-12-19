import { Injectable } from '@angular/core';
import { BehaviorSubject } from "rxjs";
import { WindowData } from "../../model/window-data";

@Injectable({
  providedIn: 'root'
})

export class WindowDataService {

	private windowDataSource = new BehaviorSubject(new WindowData());

	public windowData = this.windowDataSource.asObservable();

  	constructor() { }

  	public getWindowData() : WindowData {
  		return this.windowDataSource.getValue();
	};

	public getPageX(): number {
		let currentLoaderData: WindowData = this.windowDataSource.getValue();

		return currentLoaderData.pageX;
	}

	public setPageX(pageX: number): WindowDataService {
		let windowData = this.getWindowData();

		windowData.pageX = pageX;
		this.windowDataSource.next(windowData);

		return this;
	}

	public setPageY(pageY: number): WindowDataService {
		let windowData = this.getWindowData();

		windowData.pageY = pageY;
		this.windowDataSource.next(windowData);

		return this;
	}

}
