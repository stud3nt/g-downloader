import { Injectable } from '@angular/core';
import { BehaviorSubject } from "rxjs";
import { DownloaderStatus } from "../../enum/downloader-status";

@Injectable({
	providedIn: 'root'
})

export class DownloaderDataService {

	private statusSource = new BehaviorSubject(<string>DownloaderStatus.Idle);
	private touchSource = new BehaviorSubject(<number>0);

	public status = this.statusSource.asObservable();
	public touchEvent = this.touchSource.asObservable();

	public startDownloadProcess(): void {
		this.statusSource.next(DownloaderStatus.Downloading);
	}

	public stopDownloadProcess(): void {
		this.statusSource.next(DownloaderStatus.Idle);
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
