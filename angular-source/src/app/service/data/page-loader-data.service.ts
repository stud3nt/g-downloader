import { Injectable } from '@angular/core';
import { BehaviorSubject } from "rxjs";
import { PageLoaderData } from "../../model/page-loader-data";

@Injectable({
	providedIn: 'root'
})

export class PageLoaderDataService {

    private pageLoaderData: PageLoaderData = new PageLoaderData();

	private loaderSource = new BehaviorSubject(new PageLoaderData());
	public data = this.loaderSource.asObservable();

    /**
     * Shows loader belt
     */
	public show(): PageLoaderDataService {
        this.pageLoaderData.visible = true;
        this.pageLoaderData.autoCloseTimeout = 200;
		this.loaderSource.next(this.pageLoaderData);

  		return this;
	}

    /**
     * Hide loader belt
     * @param timeout
     */
	public hide(timeout: number = 0): PageLoaderDataService {
	    this.pageLoaderData.visible = false;
	    this.pageLoaderData.autoCloseTimeout = timeout;
  		this.loaderSource.next(this.pageLoaderData);

  		return this;
	}

    /**
     * Set progress percentage value
     * @param progress
     */
	public setProgress(progress: number = 0.00): PageLoaderDataService {
        this.pageLoaderData.progress = progress;
        this.pageLoaderData.visible = true;
		this.loaderSource.next(this.pageLoaderData);

		return this;
	}

    /**
     * Gets current progress value;
     */
	public getProgress(): number {
  	    return this.loaderSource.getValue().progress;
    }

}
