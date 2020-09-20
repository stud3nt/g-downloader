import { Injectable } from '@angular/core';
import { BehaviorSubject } from "rxjs";
import { DownloadingStatus } from "../../enum/downloading-status";

@Injectable({
	providedIn: 'root'
})

export class DownloaderDataService {

	private statusSource = new BehaviorSubject(<string>DownloadingStatus.Idle);
	private touchSource = new BehaviorSubject(<number>0);

	public status = this.statusSource.asObservable();
	public touchEvent = this.touchSource.asObservable();

	public startDownloadProcess(): void {
		this.statusSource.next(DownloadingStatus.Downloading);
	}

	public stopDownloadProcess(): void {
		this.statusSource.next(DownloadingStatus.Idle);
	}

	public getDownloadStatus(): void {
		this.statusSource.getValue();
	}

	/**
	 * Touch - change files list state function
	 */
	public touch(): void {
		this.touchSource.next(
			(new Date()).getMilliseconds()
		);
	}

}
