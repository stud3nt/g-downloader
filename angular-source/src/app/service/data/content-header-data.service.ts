import { Injectable } from '@angular/core';
import { BehaviorSubject } from "rxjs";

@Injectable({
  providedIn: 'root'
})

export class ContentHeaderDataService {

	private headerDataSource = new BehaviorSubject({
		title1: 'Dashboard',
		title2: null,
		breadcrumbs: <any>[]
	});

	public headerData = this.headerDataSource.asObservable();

  	constructor() { }

	/**
	 * Sets value to  header element (title, description?)
	 *
	 * @param elementName
	 * @param elementValue
	 */
  	public setElement(elementName: string, elementValue): void {
		let currentData = this.headerDataSource.getValue();

		currentData[elementName] = elementValue;

		this.headerDataSource.next(currentData);
	}

	/**
	 * Clear all breadcrubms;
	 */
	public clearBreadcrumbs(): void {
		let currentData = this.headerDataSource.getValue();

		currentData.breadcrumbs = [];

		this.headerDataSource.next(currentData);
	}

	/**
	 * Adds specific breadcrubm configuration to array;
	 *
	 * @param breadcrumb
	 * @param freshStart - if true, previous breadcrumbs will be resetted;
	 */
	public addBreadcrumb(breadcrumb: object = {}, freshStart: boolean = false): void {
		if (freshStart)
			this.clearBreadcrumbs();

		let currentData = this.headerDataSource.getValue();

		currentData.breadcrumbs.push(breadcrumb);
	}

}
