import { Injectable } from '@angular/core';
import { BehaviorSubject } from "rxjs";
import {PreloaderData} from "../../model/preloader-data";

@Injectable({
	providedIn: 'root'
})

export class PageLoaderDataService {

	private loaderDataSource = new BehaviorSubject(new PreloaderData());
	private loaderStatusSource = new BehaviorSubject(<object>{
		status: null, timeout: 0
	});
	private loaderProgressSource = new BehaviorSubject(<number>0);
	private loaderDescriptionSource = new BehaviorSubject(<string>'');
	private loaderProgressFromApiSource = new BehaviorSubject(<boolean>true);

	public loaderData = this.loaderDataSource.asObservable();
	public loaderStatus = this.loaderStatusSource.asObservable();
	public loaderProgress = this.loaderProgressSource.asObservable();
	public loaderDescription = this.loaderDescriptionSource.asObservable();
	public loaderProgressFromApi = this.loaderProgressFromApiSource.asObservable();

  	constructor() { }

	public getProgress() : number {
		let currentLoaderData: PreloaderData = this.loaderDataSource.getValue();
		return currentLoaderData.progress;
	}

	public setLoaderData(preloaderData: PreloaderData): PageLoaderDataService {
		this.loaderDataSource.next(preloaderData);
		return this;
	}

	public show(initial: boolean = false) : PageLoaderDataService {
		this.loaderStatusSource.next({
			status: 'show', initial: initial, timeout: 0
		});
  		return this;
	}

	public hide(timeout: number = 0) : PageLoaderDataService {
  		this.loaderStatusSource.next({
			status: 'hide', initial: false, timeout: timeout
		});
  		return this;
	}

	public setProgress(progress: number = 0): PageLoaderDataService {
		this.loaderProgressSource.next(progress);
		return this;
	}

	public setDescription(description: string = null) : PageLoaderDataService {
		this.loaderDescriptionSource.next(description);
		return this;
	}

}
